<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Csrf\TokenFetcher\StateCsrfTokenFetcher;
use Yiisoft\Csrf\TokenGenerator\RandomCsrfTokenGenerator;
use Yiisoft\Security\TokenMask;

final class CsrfTokenTest extends TestCase
{
    public function testBase(): void
    {
        $csrfToken = $this->createCsrfToken('test_token');
        $this->assertSame('test_token', TokenMask::remove($csrfToken->getValue()));
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
        return new CsrfToken(
            new StateCsrfTokenFetcher(new RandomCsrfTokenGenerator(), $mock)
        );
    }
}
