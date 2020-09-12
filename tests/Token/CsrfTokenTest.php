<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Token;

use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Token\CsrfToken;

final class CsrfTokenTest extends TestCase
{

    public function testBase(): void
    {
        $csrfToken = new CsrfToken();
        $csrfToken->setValue('test_token');
        $this->assertSame('test_token', $csrfToken->getValue());
    }

    public function testRepeatedSet(): void
    {
        $csrfToken = new CsrfToken();
        $csrfToken->setValue('test_token');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The CSRF token is already set.');
        $csrfToken->setValue('test_token');
    }

    public function testEarlyGet(): void
    {
        $csrfToken = new CsrfToken();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CSRF token is not defined.');
        $csrfToken->getValue();
    }
}