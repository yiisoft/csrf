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
            ->withHeaderName('X-JGURDA');
        $this->assertSame('X-JGURDA', $middleware->getHeaderName());
    }

    public function testImmutability(): void
    {
        $original = $this->createMiddleware();
        $this->assertNotSame($original, $original->withHeaderName('X-JGURDA'));
        $this->assertNotSame($original, $original->withUnsafeMethods([Method::POST]));
    }

    public function testDefaultUnsafeMethods(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertSame([Method::GET, Method::HEAD, Method::POST], $middleware->getUnsafeMethods());
    }

    public function testGetUnsafeMethods(): void
    {
        $methods = [Method::GET, Method::POST];
        $middleware = $this
            ->createMiddleware()
            ->withUnsafeMethods($methods);
        $this->assertSame($methods, $middleware->getUnsafeMethods());
    }

    private function createMiddleware(): CsrfHeaderMiddleware
    {
        return new CsrfHeaderMiddleware(new Psr17Factory());
    }
}
