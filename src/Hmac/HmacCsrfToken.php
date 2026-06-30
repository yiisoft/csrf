<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac;

use InvalidArgumentException;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Security\DataIsTamperedException;
use Yiisoft\Security\Mac;
use Yiisoft\Strings\StringHelper;

/**
 * Stateless CSRF token that does not require any storage. The token contains an expiration timestamp and is signed with
 * an identity-bound key. It is added to forms. When the form is submitted, we verify the token signature, check that it
 * belongs to the current identity, and check that it has not expired.
 *
 * Do not forget to decorate the token with {@see MaskedCsrfToken} to prevent BREACH attack.
 *
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#employing-hmac-csrf-tokens
 */
final class HmacCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenIdentityGeneratorInterface $identityGenerator;

    private Mac $mac;

    /**
     * @var string Shared secret key used to sign the token.
     */
    private string $secretKey;

    /**
     * @var int Hash length in bytes.
     */
    private int $hashLength;

    /**
     * @var int|null Number of seconds that the token is valid for.
     */
    private ?int $lifetime;

    public function __construct(
        CsrfTokenIdentityGeneratorInterface $identityGenerator,
        string $secretKey,
        string $algorithm = 'sha256',
        ?int $lifetime = null
    ) {
        $this->identityGenerator = $identityGenerator;
        $this->mac = new Mac($algorithm);
        $this->secretKey = $secretKey;
        $this->hashLength = $this->calcHashLength();
        $this->lifetime = $lifetime;
    }

    public function getValue(): string
    {
        return $this->generateToken(
            $this->lifetime === null ? null : (time() + $this->lifetime),
        );
    }

    public function validate(string $token): bool
    {
        $raw = $this->decode($token);
        if ($raw === null) {
            return false;
        }

        $message = $this->extractMessage($raw);
        if ($message === null) {
            return false;
        }

        if ($message !== '') {
            $expiration = (int) $message;
            if ((string) $expiration !== $message || time() > $expiration) {
                return false;
            }
        }

        try {
            $this->mac->getMessage($raw, $this->generateActualSecretKey(), true);
            return true;
        } catch (DataIsTamperedException $e) {
            return false;
        }
    }

    private function generateToken(?int $expiration): string
    {
        return StringHelper::base64UrlEncode(
            $this->mac->sign(
                (string) $expiration,
                $this->generateActualSecretKey(),
                true,
            ),
        );
    }

    private function decode(string $token): ?string
    {
        try {
            return StringHelper::base64UrlDecode($token);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    private function extractMessage(string $raw): ?string
    {
        if (StringHelper::byteLength($raw) < $this->hashLength) {
            return null;
        }

        return StringHelper::byteSubstring($raw, $this->hashLength, null);
    }

    private function generateActualSecretKey(): string
    {
        $identity = $this->identityGenerator->generate();
        return $this->secretKey . '~' . $identity;
    }

    private function calcHashLength(): int
    {
        return StringHelper::byteLength($this->mac->sign('', '', true));
    }
}
