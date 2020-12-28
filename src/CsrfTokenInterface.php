<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

/**
 * Token returns currently valid token as string.
 */
interface CsrfTokenInterface
{
    /**
     * @return string The currently valid token as string.
     */
    public function getValue(): string;

    /**
     * @param string $token Token string to validate.
     *
     * @return bool If token string is valid.
     */
    public function validate(string $token): bool;
}
