<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenGenerator;

/**
 * Token generator generates a new CSRF token.
 * @internal Do not use directly.
 */
interface CsrfTokenGeneratorInterface
{
    public function generate(): string;
}
