<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateful;

/**
 * Token generator generates a new CSRF token.
 */
interface CsrfTokenGeneratorInterface
{
    /**
     * Generate a new CSRF token.
     *
     * @return string CSRF token string.
     */
    public function generate(): string;
}
