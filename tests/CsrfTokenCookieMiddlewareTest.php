<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\CsrfTokenCookieMiddleware;
use Yiisoft\Csrf\StubCsrfToken;
use Yiisoft\Http\Method;

final class CsrfTokenCookieMiddlewareTest extends TestCase
{
    public function testInvalidSameSite(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "SameSite" attribute value "Weak" is not valid.');

        new CsrfTokenCookieMiddleware(new StubCsrfToken(), 'XSRF-TOKEN', '/', null, true, 'Weak');
    }

    public function testSameSiteNoneWithoutSecure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The "secure" flag is required for cookies with "SameSite" attribute set to "None".',
        );

        new CsrfTokenCookieMiddleware(
            new StubCsrfToken(),
            'XSRF-TOKEN',
            '/',
            null,
            false,
            CsrfTokenCookieMiddleware::SAME_SITE_NONE,
        );
    }

    public function testDefaults(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(new StubCsrfToken('test-token'));

        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertSame(
            ['XSRF-TOKEN=test-token; Path=/; Secure; SameSite=Lax'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testTokenValueIsUrlEncoded(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(new StubCsrfToken('a b+c/d='));

        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertSame(
            ['XSRF-TOKEN=a+b%2Bc%2Fd%3D; Path=/; Secure; SameSite=Lax'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testCustomAttributes(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(
            new StubCsrfToken('test-token'),
            'MY-TOKEN',
            '/api',
            'example.com',
            false,
            CsrfTokenCookieMiddleware::SAME_SITE_STRICT,
        );

        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertSame(
            ['MY-TOKEN=test-token; Domain=example.com; Path=/api; SameSite=Strict'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testWithoutSameSite(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(
            new StubCsrfToken('test-token'),
            'XSRF-TOKEN',
            '/',
            null,
            true,
            null,
        );

        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertSame(
            ['XSRF-TOKEN=test-token; Path=/; Secure'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testCookieIsPublishedForUnsafeMethods(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(new StubCsrfToken('test-token'));

        $response = $middleware->process(
            $this->createServerRequest(Method::POST),
            $this->createRequestHandler(),
        );

        $this->assertSame(
            ['XSRF-TOKEN=test-token; Path=/; Secure; SameSite=Lax'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testExistingResponseCookiesArePreserved(): void
    {
        $middleware = new CsrfTokenCookieMiddleware(new StubCsrfToken('test-token'));

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn((new Response())->withAddedHeader('Set-Cookie', 'session=abc; Path=/'));

        $response = $middleware->process($this->createServerRequest(), $requestHandler);

        $this->assertSame(
            [
                'session=abc; Path=/',
                'XSRF-TOKEN=test-token; Path=/; Secure; SameSite=Lax',
            ],
            $response->getHeader('Set-Cookie'),
        );
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function createServerRequest(string $method = Method::GET): ServerRequestInterface
    {
        return new ServerRequest($method, '/');
    }
}
