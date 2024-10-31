<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Csrf\Tests\Synchronizer\Storage\MockCsrfTokenStorage;
use Yiisoft\Http\Method;

final class CsrfMiddlewareTest extends TestCase
{
    public function testDefaultParameterName(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame(CsrfMiddleware::PARAMETER_NAME, $middleware->getParameterName());
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
        $this->assertSame(CsrfMiddleware::HEADER_NAME, $middleware->getHeaderName());
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
        $this->assertNotSame($original, $original->withSafeMethods([Method::HEAD]));
    }

    private function createMiddleware(): CsrfMiddleware
    {
        return new CsrfMiddleware(
            new Psr17Factory(),
            new SynchronizerCsrfToken(
                new RandomCsrfTokenGenerator(),
                new MockCsrfTokenStorage()
            )
        );
    }
}
