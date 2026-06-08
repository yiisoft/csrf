<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac;

use RuntimeException;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Security\Random;
use Yiisoft\Strings\StringHelper;

use function count;
use function hash_equals;
use function hash_hmac;

/**
 * Stateless CSRF token that does not require any storage. The token contains expiration timestamp and random value,
 * and is signed with a session-bound identity. It is added to forms. When the form is submitted, we verify the token
 * signature, check that it belongs to the current session identity, and check that it has not expired.
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

        $hashLength = $this->getHashLength();
        $hash = StringHelper::byteSubstring($payload, 0, $hashLength);
        $message = StringHelper::byteSubstring($payload, $hashLength, null);

        if (!hash_equals($hash, $this->generateHash($message))) {
            return false;
        }

        if ($expiration !== null && time() > $expiration) {
            return false;
        }
        return true;
    }

    private function generateToken(?int $expiration): string
    {
        $message = (string) $expiration . '~' . Random::string(32);

        return StringHelper::base64UrlEncode($this->generateHash($message) . $message);
    }

    /**
     * @return array{0: int|null, 1: string}|null
     */
    private function extractData(string $token): ?array
    {
        $payload = StringHelper::base64UrlDecode($token);
        $hashLength = $this->getHashLength();

        if (StringHelper::byteLength($payload) <= $hashLength) {
            return null;
        }

        $message = StringHelper::byteSubstring($payload, $hashLength, null);
        $chunks = explode('~', $message, 2);
        if (count($chunks) !== 2) {
            return null;
        }

        if ($chunks[0] === '') {
            $expiration = null;
        } else {
            $expiration = (int) $chunks[0];
            if ((string) $expiration !== $chunks[0]) {
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

    private function getHashLength(): int
    {
        return StringHelper::byteLength($this->generateHash(''));
    }
}
