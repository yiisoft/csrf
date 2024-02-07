<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Yiisoft\Security\Random;

/**
 * StubCsrfToken represents a simple implementation of CsrfTokenInterface.
 *
 * This implementation simply stores and returns a token string. It does not perform any additional validation.
 * It is primarily used for testing or as a placeholder implementation.
 */
final class StubCsrfToken implements CsrfTokenInterface
{
    private string $token;

    public function __construct(?string $token = null)
    {
        if (null === $token) {
            $token = Random::string();
        }
        $this->token = $token;
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function validate(string $token): bool
    {
        return $this->token === $token;
    }
}
