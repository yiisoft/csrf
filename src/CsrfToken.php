<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;

final class CsrfToken implements CsrfTokenInterface
{

    private ?string $token = null;

    public function getValue(): ?string
    {
        return $this->token;
    }

    public function setValue(string $token): void
    {
        if ($this->token !== null) {
            throw new LogicException('The CSRF token is already set.');
        }
        $this->token = $token;
    }
}
