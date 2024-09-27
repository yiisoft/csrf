<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\CsrfHeaderMiddleware;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Security\Random;

final class CsrfHeaderMiddlewareProcessTest extends TestCase
{
    public function testOptionsIsAlwaysAllowed(): void
    {
        $middleware = $this->createMiddleware();
        $response = $middleware->process(
            $this->createServerRequest(Method::OPTIONS),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCustomSafeGetRequestResultIn200(): void
    {
        $middleware = $this
            ->createMiddleware()
            ->withSafeMethods([Method::GET, Method::HEAD, Method::OPTIONS]);
        $response = $middleware->process(
            $this->createServerRequest(Method::GET),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUnsafeMethodGetRequestResultIn422(): void
    {
        $middleware = $this->createMiddleware();
        $response = $middleware->process(
            $this->createServerRequest(Method::GET),
            $this->createRequestHandler()
        );
        $this->assertEquals(Status::TEXTS[Status::UNPROCESSABLE_ENTITY], $response->getBody());
        $this->assertEquals(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCustomUnsafeMethodPostRequestResultIn422(): void
    {
        $middleware = $this
            ->createMiddleware()
            ->withSafeMethods([Method::GET, Method::HEAD, Method::OPTIONS]);
        $response = $middleware->process(
            $this->createServerRequest(Method::POST),
            $this->createRequestHandler()
        );
        $this->assertEquals(Status::TEXTS[Status::UNPROCESSABLE_ENTITY], $response->getBody());
        $this->assertEquals(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testValidCustomHeaderResultIn200(): void
    {
        $headerName = 'X-MY-CSRF';

        $middleware = $this
            ->createMiddleware()
            ->withHeaderName($headerName)
            ->withSafeMethods([Method::GET]);
        $response = $middleware->process(
            $this->createServerRequest(Method::GET, [$headerName => Random::string()]),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmptyTokenInRequestResultIn200(): void
    {
        $middleware = $this->createMiddleware();
        $response = $middleware->process(
            $this->createServerRequest(Method::GET, [CsrfHeaderMiddleware::HEADER_NAME => '']),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidHeaderResultIn422(): void
    {
        $middleware = $this->createMiddleware();
        $response = $middleware->process(
            $this->createServerRequest(Method::POST, ['X-MY-CSRF' => '']),
            $this->createRequestHandler()
        );
        $this->assertEquals(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(Status::TEXTS[Status::UNPROCESSABLE_ENTITY], $response->getBody());
    }

    public function testInvalidHeaderResultWithCustomFailureHandler(): void
    {
        $failureHandler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new Response(Status::BAD_REQUEST);
                $response
                    ->getBody()
                    ->write(Status::TEXTS[Status::BAD_REQUEST]);
                return $response;
            }
        };
        $middleware = $this->createMiddleware($failureHandler);
        $response = $middleware->process(
            $this->createServerRequest(Method::POST, ['X-MY-CSRF' => '']),
            $this->createRequestHandler(),
        );
        $this->assertEquals(Status::BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(Status::TEXTS[Status::BAD_REQUEST], $response->getBody());
    }

    private function createMiddleware(
        RequestHandlerInterface $failureHandler = null
    ): CsrfHeaderMiddleware {
        return new CsrfHeaderMiddleware(new Psr17Factory(), $failureHandler);
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function createServerRequest(
        string $method = Method::GET,
        array $headParams = []
    ): ServerRequestInterface {
        return new ServerRequest($method, '/', $headParams);
    }
}
