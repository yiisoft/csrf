<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;

final class CsrfToken
{

    private ?string $token = null;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        if ($this->token !== null) {
            throw new LogicException('The CSRF token is already set.');
        }
        $this->token = $token;
    }
}
