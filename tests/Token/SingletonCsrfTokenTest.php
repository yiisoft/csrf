<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Token;

use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Token\SingletonCsrfToken;

final class SingletonCsrfTokenTest extends TestCase
{

    public function testBase(): void
    {
        $csrfToken = new SingletonCsrfToken();
        $csrfToken->setValue('test_token');
        $this->assertSame('test_token', $csrfToken->getValue());
    }

    public function testRepeatedSet(): void
    {
        $csrfToken = new SingletonCsrfToken();
        $csrfToken->setValue('test_token');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The CSRF token is already set.');
        $csrfToken->setValue('test_token');
    }

    public function testEarlyGet(): void
    {
        $csrfToken = new SingletonCsrfToken();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CSRF token is not defined.');
        $csrfToken->getValue();
    }
}
