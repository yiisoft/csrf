<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenStorage;

final class CsrfTokenTest extends TestCase
{

//    public function testBase(): void
//    {
//        $csrfToken = $this->createCsrfToken('test_token');
//        $this->assertSame('test_token', $csrfToken->getValue());
//    }

    public function testEarlyGet(): void
    {
        $csrfToken = $this->createCsrfToken();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CSRF token is not defined.');
        $csrfToken->getValue();
    }

    private function createCsrfToken(string $token = null): CsrfToken
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);
        if ($token !== null) {
            $mock
                ->expects($this->once())
                ->method('get')
                ->willReturn($token);
        }
        return new CsrfToken($mock);
    }
}
