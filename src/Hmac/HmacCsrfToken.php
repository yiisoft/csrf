<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac;

use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Strings\StringHelper;

use function hash_equals;
use function hash_hmac;

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

    /**
     * @var string Shared secret key used to generate the hash.
     */
    private string $secretKey;

    /**
     * @var string Hash algorithm for message authentication.
     */
    private string $algorithm;

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
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
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
        $data = $this->extractData($token);
        if ($data === null) {
            return false;
        }

        [$expiration, $payload] = $data;

        if ($expiration !== null && time() > $expiration) {
            return false;
        }

        $hash = StringHelper::byteSubstring($payload, 0, $this->hashLength);
        $message = StringHelper::byteSubstring($payload, $this->hashLength, null);

        if (!hash_equals($hash, $this->generateHash($message))) {
            return false;
        }

        return true;
    }

    private function generateToken(?int $expiration): string
    {
        $message = (string) $expiration;

        return StringHelper::base64UrlEncode($this->generateHash($message) . $message);
    }

    /**
     * @return array{0: int|null, 1: string}|null
     */
    private function extractData(string $token): ?array
    {
        try {
            $payload = StringHelper::base64UrlDecode($token);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        if (StringHelper::byteLength($payload) < $this->hashLength) {
            return null;
        }

        $message = StringHelper::byteSubstring($payload, $this->hashLength, null);
        if ($message === '') {
            $expiration = null;
        } else {
            $expiration = (int) $message;
            if ((string) $expiration !== $message) {
                return null;
            }
        }

        return [$expiration, $payload];
    }

    private function generateHash(string $message): string
    {
        $identity = $this->identityGenerator->generate();
        $message = StringHelper::byteLength($identity) . '~' . $identity . '~' . $message;
        $hash = hash_hmac($this->algorithm, $message, $this->secretKey, true);
        if (!$hash) {
            throw new RuntimeException("Failed to generate HMAC with hash algorithm: {$this->algorithm}.");
        }
        return $hash;
    }

    private function calcHashLength(): int
    {
        $hash = hash_hmac($this->algorithm, '', '', true);
        if (!$hash) {
            throw new RuntimeException("Failed to generate HMAC with hash algorithm: {$this->algorithm}.");
        }
        return StringHelper::byteLength($hash);
    }
}
