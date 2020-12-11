<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Token;

interface CsrfTokenInterface
{
    public function generate(): string;
}
