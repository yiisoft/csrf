<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

/**
 * `EmptyCsrfToken` represents an implementation of `CsrfTokenInterface` with empty value.
 */
final class EmptyCsrfToken implements CsrfTokenInterface
{
    public function getValue(): string
    {
        return '';
    }

    public function validate(string $token): bool
    {
        return true;
    }
}
