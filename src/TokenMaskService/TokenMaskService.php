<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenMaskService;

use Yiisoft\Security\TokenMask;

final class TokenMaskService implements TokenMaskServiceInterface
{

    public function apply(string $token): string
    {
        return TokenMask::apply($token);
    }

    public function remove(string $token): string
    {
        return TokenMask::remove($token);
    }
}
