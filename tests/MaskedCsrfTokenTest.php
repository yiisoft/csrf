<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Csrf\Stateful\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Stateful\StatefulCsrfToken;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Security\TokenMask;

final class MaskedCsrfTokenTest extends TestCase
{
    public function testBase(): void
    {
        $csrfToken = $this->createCsrfToken('test_token');
        $this->assertSame('test_token', TokenMask::remove($csrfToken->getValue()));
    }

    private function createCsrfToken(string $token = null): MaskedCsrfToken
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);
        if ($token !== null) {
            $mock
                ->expects($this->once())
                ->method('get')
                ->willReturn($token);
        }
        return new MaskedCsrfToken(
            new StatefulCsrfToken(new RandomCsrfTokenGenerator(), $mock)
        );
    }
}
