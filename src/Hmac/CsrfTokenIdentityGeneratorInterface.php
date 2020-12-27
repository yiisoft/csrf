<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac;

interface CsrfTokenIdentityGeneratorInterface
{
    public function generate(): string;
}