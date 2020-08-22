<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;

final class CsrfToken
{

    private static ?string $token = null;

    public static function getValue(): ?string
    {
        return static::$token;
    }

    public static function setValue(string $token): void
    {
        if (static::$token !== null) {
            throw new LogicException('The CSRF token is already set.');
        }
        static::$token = $token;
    }
}
