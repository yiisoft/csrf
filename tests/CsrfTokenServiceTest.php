<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfTokenService;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Csrf\Token\RandomCsrfToken;
use Yiisoft\Security\TokenMask;

final class CsrfTokenServiceTest extends TestCase
{
    public function testBase(): void
    {
        $service = $this->createService('test_token');
        $this->assertSame('test_token', TokenMask::remove($service->getValue()));
    }

    private function createService(string $token = null): CsrfTokenService
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);
        if ($token !== null) {
            $mock
                ->expects($this->once())
                ->method('get')
                ->willReturn($token);
        }

        return new CsrfTokenService(new RandomCsrfToken(), $mock);
    }
}
