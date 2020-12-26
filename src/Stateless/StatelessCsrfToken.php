<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Security\Random;
use Yiisoft\Strings\StringHelper;

final class StatelessCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenIdentificationInterface $identification;
    private string $algorithm;
    private string $secretKey;
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

        $hash = $chunks[0];
        $salt = $chunks[1];

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
