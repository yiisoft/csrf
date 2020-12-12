<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenGenerator;

interface CsrfTokenGeneratorInterface
{
    public function generate(): string;
}
