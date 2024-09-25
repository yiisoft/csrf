<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfHeaderMiddleware;
use Yiisoft\Http\Method;

final class CsrfHeaderMiddlewareTest extends TestCase
{
    public function testDefaultHeaderName(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame(CsrfHeaderMiddleware::HEADER_NAME, $middleware->getHeaderName());
    }

    public function testGetHeaderName(): void
    {
        $middleware = $this
            ->createMiddleware()
            ->withHeaderName('X-MY-CSRF');
        $this->assertSame('X-MY-CSRF', $middleware->getHeaderName());
    }

    public function testImmutability(): void
    {
        $original = $this->createMiddleware();
        $this->assertNotSame($original, $original->withHeaderName('X-MY-CSRF'));
        $this->assertNotSame($original, $original->withSafeMethods([Method::HEAD]));
    }

    public function testDefaultSafeMethods(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame([Method::OPTIONS], $middleware->getSafeMethods());
    }

    public function testGetSafeMethods(): void
    {
        $methods = [Method::GET, Method::HEAD, Method::OPTIONS];
        $middleware = $this
            ->createMiddleware()
            ->withSafeMethods($methods);
        $this->assertSame($methods, $middleware->getSafeMethods());
    }

    private function createMiddleware(): CsrfHeaderMiddleware
    {
        return new CsrfHeaderMiddleware(new Psr17Factory());
    }
}
