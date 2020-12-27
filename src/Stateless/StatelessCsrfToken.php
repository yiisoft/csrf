<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Security\DataIsTamperedException;
use Yiisoft\Security\Mac;
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
    private Mac $mac;

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
        $this->mac = new Mac($algorithm);
        $this->secretKey = $secretKey;
        $this->lifetime = $lifetime;
    }

    public function getValue(): string
    {
        return $this->generateToken(
            $this->lifetime === null ? null : (time() + $this->lifetime)
        );
    }

    public function validate(string $token): bool
    {
        try {
            [$expiration, $identification] = $this->extractData($token);
        } catch (DataIsTamperedException $e) {
            return false;
        }

        if ($expiration !== null && time() > $expiration) {
            return false;
        }

        return $identification === $this->identification->getString();
    }

    private function generateToken(?int $expiration): string
    {
        return StringHelper::base64UrlEncode(
            $this->mac->sign(
                (string)$expiration . '~' . $this->identification->getString(),
                $this->secretKey,
                true
            )
        );
    }

    private function extractData(string $token): array
    {
        $raw = $this->mac->getMessage(
            StringHelper::base64UrlDecode($token),
            $this->secretKey,
            true
        );

        $chunks = explode('~', $raw, 2);
        if (count($chunks) !== 2) {
            throw new DataIsTamperedException();
        }

        if ($chunks[0] === '') {
            $expiration = null;
        } else {
            $expiration = (int)$chunks[0];
            if ((string)$expiration !== $chunks[0]) {
                throw new DataIsTamperedException();
            }
        }

        $identification = $chunks[1];

        return [$expiration, $identification];
    }
}
