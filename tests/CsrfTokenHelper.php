<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use ReflectionClass;
use Yiisoft\Csrf\CsrfToken;

class CsrfTokenHelper
{

    public static function reset()
    {
        $ref = new ReflectionClass(CsrfToken::class);
        $property = $ref->getProperty('token');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
