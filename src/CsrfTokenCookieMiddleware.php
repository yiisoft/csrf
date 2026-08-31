<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Header;

use function gettype;
use function implode;
use function in_array;
use function is_bool;
use function is_string;
use function preg_match;
use function rawurlencode;
use function sprintf;

/**
 * PSR-15 middleware that publishes the current CSRF token in a JavaScript-readable response cookie.
 *
 * It is intended for AJAX/SPA clients that read the token from a cookie and send it back explicitly in a request
 * header (the "cookie-to-header" pattern), for example Inertia and Axios which use the `XSRF-TOKEN` cookie and the
 * `X-XSRF-TOKEN` header.
 *
 * The middleware only writes the cookie. Token validation stays the responsibility of {@see CsrfTokenMiddleware},
 * which reads the submitted token from a header or a body parameter. Place this middleware before
 * {@see CsrfTokenMiddleware} in the stack, so the cookie is published even on a rejected request.
 *
 * A `Set-Cookie` header does not by itself prevent a response from being cached (RFC 9111 section 7.3). A cached
 * response may replay a stale token, or reach a shared (proxy or CDN) cache and hand one user's token to another. By
 * default the middleware guards against this by sending `Cache-Control: no-store` for responses that carry no
 * `Cache-Control` of their own; see the `$cacheControl` constructor parameter to change or disable this.
 *
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#alternative-using-a-double-submit-cookie-pattern
 * @link https://www.rfc-editor.org/rfc/rfc9111#section-7.3
 */
final class CsrfTokenCookieMiddleware implements MiddlewareInterface
{
    public const COOKIE_NAME = 'XSRF-TOKEN';

    public const SAME_SITE_LAX = 'Lax';
    public const SAME_SITE_STRICT = 'Strict';
    public const SAME_SITE_NONE = 'None';

    /**
     * Control characters (including CR and LF) and the `;` attribute separator. These would let a configured
     * cookie name, path or domain inject extra attributes or split the response header. Whether the values are
     * otherwise well-formed is left to the caller.
     */
    private const PATTERN_HEADER_INJECTION = '/[\x00-\x1F\x7F\x3B]/';

    private CsrfTokenInterface $token;
    private string $cookieName;
    private string $path;
    private ?string $domain;
    private ?bool $secure;
    private ?string $sameSite;

    /**
     * @var bool|string
     */
    private $cacheControl;

    /**
     * The cookie name, path and domain are checked only for control characters and `;` to prevent response header
     * injection; making sure they are otherwise valid is up to the caller.
     *
     * @param CsrfTokenInterface $token The CSRF token to publish.
     * @param string $cookieName The name of the cookie holding the token.
     * @param string $path The `Path` attribute of the cookie.
     * @param string|null $domain The `Domain` attribute of the cookie, or `null` to omit it.
     * @param bool|null $secure Whether the cookie should only be sent over HTTPS. When `null`, the value is resolved
     * per request: `true` for `self::SAME_SITE_NONE` (browsers require `Secure` for such cookies), otherwise from the
     * request URI scheme (`true` when the scheme is `https`).
     * @param string|null $sameSite The `SameSite` attribute of the cookie: one of `self::SAME_SITE_LAX`,
     * `self::SAME_SITE_STRICT`, `self::SAME_SITE_NONE`, or `null` to omit it. When `self::SAME_SITE_NONE` is used,
     * `$secure` must not be `false`.
     * @param bool|string $cacheControl How to handle the `Cache-Control` response header: `true` to send
     * `Cache-Control: no-store` when the response has no `Cache-Control` of its own (keeping the published token out
     * of any cache), `false` to leave the header untouched, or a string to set it to that exact value.
     *
     * @throws InvalidArgumentException When a cookie attribute or the `$cacheControl` argument is not valid.
     */
    public function __construct(
        CsrfTokenInterface $token,
        string $cookieName = self::COOKIE_NAME,
        string $path = '/',
        ?string $domain = null,
        ?bool $secure = null,
        ?string $sameSite = self::SAME_SITE_LAX,
        $cacheControl = true
    ) {
        if (preg_match(self::PATTERN_HEADER_INJECTION, $cookieName)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $cookieName),
            );
        }

        if (preg_match(self::PATTERN_HEADER_INJECTION, $path)) {
            throw new InvalidArgumentException(
                sprintf('The cookie path "%s" contains invalid characters.', $path),
            );
        }

        if ($domain !== null && preg_match(self::PATTERN_HEADER_INJECTION, $domain)) {
            throw new InvalidArgumentException(
                sprintf('The cookie domain "%s" contains invalid characters.', $domain),
            );
        }

        $allowedSameSite = [self::SAME_SITE_LAX, self::SAME_SITE_STRICT, self::SAME_SITE_NONE];
        if ($sameSite !== null && !in_array($sameSite, $allowedSameSite, true)) {
            throw new InvalidArgumentException(
                sprintf('The "SameSite" attribute value "%s" is not valid.', $sameSite),
            );
        }

        if ($sameSite === self::SAME_SITE_NONE && $secure === false) {
            throw new InvalidArgumentException(
                'The "secure" flag is required for cookies with "SameSite" attribute set to "None".',
            );
        }

        /**
         * The parameter has no native type because a `bool|string` union is not available on PHP 7.4, so a value of
         * any other type can still be passed at runtime. Once the minimum PHP version is 8.0+, type the parameter as
         * `bool|string` and remove this check together with the suppression below.
         *
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!is_bool($cacheControl) && !is_string($cacheControl)) {
            throw new InvalidArgumentException(
                sprintf('The "cacheControl" argument must be a bool or a string, "%s" given.', gettype($cacheControl)),
            );
        }

        $this->token = $token;
        $this->cookieName = $cookieName;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->sameSite = $sameSite;
        $this->cacheControl = $cacheControl;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // An explicit `$secure` wins; when it is `null`, `SameSite=None` forces `Secure`, otherwise the request
        // URI scheme decides.
        $secure = $this->secure
            ?? ($this->sameSite === self::SAME_SITE_NONE || $request->getUri()->getScheme() === 'https');
        $response = $response->withAddedHeader(Header::SET_COOKIE, $this->buildCookieHeaderValue($secure));

        return $this->applyCacheControl($response);
    }

    private function buildCookieHeaderValue(bool $secure): string
    {
        $parts = [$this->cookieName . '=' . rawurlencode($this->token->getValue())];

        if ($this->domain !== null) {
            $parts[] = 'Domain=' . $this->domain;
        }

        $parts[] = 'Path=' . $this->path;

        if ($secure) {
            $parts[] = 'Secure';
        }

        if ($this->sameSite !== null) {
            $parts[] = 'SameSite=' . $this->sameSite;
        }

        return implode('; ', $parts);
    }

    private function applyCacheControl(ResponseInterface $response): ResponseInterface
    {
        if ($this->cacheControl === false) {
            return $response;
        }

        if ($this->cacheControl === true) {
            return $response->hasHeader(Header::CACHE_CONTROL)
                ? $response
                : $response->withHeader(Header::CACHE_CONTROL, 'no-store');
        }

        return $response->withHeader(Header::CACHE_CONTROL, $this->cacheControl);
    }
}
