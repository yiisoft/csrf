<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

/**
 * Token generator generates a new CSRF token.
 */
interface CsrfTokenGeneratorInterface
{
    public function generate(): string;
}
