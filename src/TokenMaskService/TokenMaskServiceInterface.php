<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenMaskService;

interface TokenMaskServiceInterface
{

    public function apply(string $token): string;

    public function remove(string $token): string;
}
