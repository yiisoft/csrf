<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

interface CsrfTokenIdentityGeneratorInterface
{
    public function generate(): string;
}
