<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Token;

use LogicException;

/**
 * Class should be singleton
 */
final class SingletonCsrfToken implements CsrfTokenInterface
{

    private ?string $value = null;

    public function getValue(): ?string
    {
        if ($this->value === null) {
            throw new LogicException('CSRF token is not defined.');
        }
        return $this->value;
    }

    public function setValue(string $token): void
    {
        if ($this->value !== null) {
            throw new LogicException('The CSRF token is already set.');
        }
        $this->value = $token;
    }
}
