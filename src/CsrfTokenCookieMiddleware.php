<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Header;

use function implode;
use function in_array;
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
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#alternative-using-a-double-submit-cookie-pattern
 */
final class CsrfTokenCookieMiddleware implements MiddlewareInterface
{
    public const COOKIE_NAME = 'XSRF-TOKEN';

    public const SAME_SITE_LAX = 'Lax';
    public const SAME_SITE_STRICT = 'Strict';
    public const SAME_SITE_NONE = 'None';

    private CsrfTokenInterface $token;
    private string $cookieName;
    private string $path;
    private ?string $domain;
    private bool $secure;
    private ?string $sameSite;

    /**
     * @param CsrfTokenInterface $token The CSRF token to publish.
     * @param string $cookieName The name of the cookie holding the token.
     * @param string $path The path attribute of the cookie.
     * @param string|null $domain The domain attribute of the cookie, or `null` to omit it.
     * @param bool $secure Whether the cookie should only be sent over HTTPS.
     * @param string|null $sameSite The `SameSite` attribute of the cookie: one of `self::SAME_SITE_LAX`,
     * `self::SAME_SITE_STRICT`, `self::SAME_SITE_NONE`, or `null` to omit it. When `self::SAME_SITE_NONE` is used,
     * `$secure` must be `true`.
     */
    public function __construct(
        CsrfTokenInterface $token,
        string $cookieName = self::COOKIE_NAME,
        string $path = '/',
        ?string $domain = null,
        bool $secure = true,
        ?string $sameSite = self::SAME_SITE_LAX
    ) {
        $allowedSameSite = [self::SAME_SITE_LAX, self::SAME_SITE_STRICT, self::SAME_SITE_NONE];
        if ($sameSite !== null && !in_array($sameSite, $allowedSameSite, true)) {
            throw new InvalidArgumentException(
                sprintf('The "SameSite" attribute value "%s" is not valid.', $sameSite),
            );
        }

        if ($sameSite === self::SAME_SITE_NONE && !$secure) {
            throw new InvalidArgumentException(
                'The "secure" flag is required for cookies with "SameSite" attribute set to "None".',
            );
        }

        $this->token = $token;
        $this->cookieName = $cookieName;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->sameSite = $sameSite;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withAddedHeader(Header::SET_COOKIE, $this->buildCookieHeaderValue());
    }

    private function buildCookieHeaderValue(): string
    {
        $parts = [$this->cookieName . '=' . rawurlencode($this->token->getValue())];

        if ($this->domain !== null) {
            $parts[] = 'Domain=' . $this->domain;
        }

        $parts[] = 'Path=' . $this->path;

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->sameSite !== null) {
            $parts[] = 'SameSite=' . $this->sameSite;
        }

        return implode('; ', $parts);
    }
}
