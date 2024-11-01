<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Csrf\Tests\Synchronizer\Storage\MockCsrfTokenStorage;

final class CsrfMiddlewareTest extends TestCase
{
    public function testDefaultParameterName(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame(CsrfTokenMiddleware::PARAMETER_NAME, $middleware->getParameterName());
    }

    public function testGetParameterName(): void
    {
        $middleware = $this
            ->createMiddleware()
            ->withParameterName('my-csrf');
        $this->assertSame('my-csrf', $middleware->getParameterName());
    }

    public function testDefaultHeaderName(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame(CsrfTokenMiddleware::HEADER_NAME, $middleware->getHeaderName());
    }

    public function testGetHeaderName(): void
    {
        $middleware = $this
            ->createMiddleware()
            ->withHeaderName('MY-CSRF');
        $this->assertSame('MY-CSRF', $middleware->getHeaderName());
    }

    public function testImmutability(): void
    {
        $original = $this->createMiddleware();
        $this->assertNotSame($original, $original->withHeaderName('csrf'));
        $this->assertNotSame($original, $original->withParameterName('csrf'));
    }

    private function createMiddleware(): CsrfTokenMiddleware
    {
        return new CsrfTokenMiddleware(
            new Psr17Factory(),
            new SynchronizerCsrfToken(
                new RandomCsrfTokenGenerator(),
                new MockCsrfTokenStorage()
            )
        );
    }
}
