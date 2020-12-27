<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenIdentityGenerator;

final class HmacCsrfTokenTest extends TestCase
{
    public function testBase(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey'
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
        $this->assertFalse($csrfToken->validate('hello_world'));
    }
}
