<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

interface CsrfTokenIdentificationInterface
{
    public function getString(): string;
}
