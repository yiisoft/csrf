<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\Tests\Hmac\IdentityGenerator\MockCsrfTokenIdentityGenerator;
use Yiisoft\Csrf\Tests\DeprecatedTokenCsrfMiddlewareTest;
use Yiisoft\Security\Random;

final class DeprecatedHmacTokenCsrfMiddlewareTest extends DeprecatedTokenCsrfMiddlewareTest
{
    protected function createCsrfToken(): CsrfTokenInterface
    {
        return new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator(Random::string()),
            'secretKey'
        );
    }
}
