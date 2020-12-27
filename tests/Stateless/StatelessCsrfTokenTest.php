<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Stateless;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Stateless\StatelessCsrfToken;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenIdentityGenerator;

final class StatelessCsrfTokenTest extends TestCase
{
    public function testBase(): void
    {
        $csrfToken = new StatelessCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey'
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
        $this->assertFalse($csrfToken->validate('hello_world'));
    }
}
