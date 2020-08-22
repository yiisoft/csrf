<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

final class CsrfToken
{

    private static ?string $token = null;

    public static function getValue(): ?string
    {
        return static::$token;
    }

    public static function setValue(string $token): void
    {
        static::$token = $token;
    }
}
