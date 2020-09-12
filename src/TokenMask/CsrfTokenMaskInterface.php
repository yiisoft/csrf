<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenMask;

interface CsrfTokenMaskInterface
{

    public function apply(string $token): string;

    public function remove(string $token): string;
}
