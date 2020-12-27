<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Security\Random;
use Yiisoft\Strings\StringHelper;

/**
 * Stateless CSRF token does not require any storage. The token is a hash from session ID and a timestamp
 * (to prevent replay attacks). It is added to forms. When the form is submitted, we re-generate the token from
 * the current session ID and a timestamp from the original token. If two hashes match, we check that timestamp is
 * less than {@see StatelessCsrfToken::$lifetime}.
 *
 * The algorithm is also known as "HMAC Based Token".
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#hmac-based-token-pattern
 */
final class StatelessCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenIdentificationInterface $identification;

    /**
     * @var string Token hashing algorithm.
     */
    private string $algorithm;

    /**
     * @var string Shared secret key used for generating the hash.
     */
    private string $secretKey;

    /**
     * @var int|null Number of seconds that the token is valid for.
     */
    private ?int $lifetime;

    public function __construct(
        CsrfTokenIdentificationInterface $identification,
        string $secretKey,
        string $algorithm = 'sha256',
        ?int $lifetime = null
    ) {
        $this->identification = $identification;
        $this->algorithm = $algorithm;
        $this->secretKey = $secretKey;
        $this->lifetime = $lifetime;
    }

    public function getValue(): string
    {
        $salt = Random::string(8);
        $expiration = $this->lifetime === null ? null : (time() + $this->lifetime);

        return StringHelper::base64UrlEncode(
            $this->generateHash($salt, $expiration) . '~' . $salt . '~' . (string)$expiration
        );
    }

    public function validate(string $token): bool
    {
        $chunks = explode('~', StringHelper::base64UrlDecode($token));
        if (count($chunks) !== 3) {
            return false;
        }

        [$hash, $salt] = $chunks;

        if ($chunks[2] === '') {
            $expiration = null;
        } else {
            $expiration = (int)$chunks[2];
            if ((string)$expiration !== $chunks[2]) {
                return false;
            }
        }

        if ($expiration !== null && time() > $expiration) {
            return false;
        }

        return hash_equals(
            $hash,
            $this->generateHash($salt, $expiration)
        );
    }

    private function generateHash(string $salt, ?int $expiration): string
    {
        return StringHelper::base64UrlEncode(
            hash_hmac(
                $this->algorithm,
                $salt . (string)$expiration . $this->identification->getString(),
                $this->secretKey,
                true
            )
        );
    }
}
