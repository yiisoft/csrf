<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenMask;

use Yiisoft\Security\TokenMask;

final class CsrfTokenMask implements CsrfTokenMaskInterface
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
