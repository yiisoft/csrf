<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\CsrfTokenStorageInterface;
use Yiisoft\Csrf\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Http\Method;
use Yiisoft\Security\Random;
use Yiisoft\Security\TokenMask;

final class CsrfMiddlewareTest extends TestCase
{

    private const PARAM_NAME = 'csrf';

    public function testValidTokenInBodyPostRequestResultIn200(): void
    {
        $token = $this->generateToken();
        $middleware = $this->createCsrfMiddlewareWithToken($token);
        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken($token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInBodyPutRequestResultIn200(): void
    {
        $token = $this->generateToken();
        $middleware = $this->createCsrfMiddlewareWithToken($token);
        $response = $middleware->process(
            $this->createPutServerRequestWithBodyToken($token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInBodyDeleteRequestResultIn200(): void
    {
        $token = $this->generateToken();
        $middleware = $this->createCsrfMiddlewareWithToken($token);
        $response = $middleware->process(
            $this->createDeleteServerRequestWithBodyToken($token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInHeaderResultIn200(): void
    {
        $token = $this->generateToken();
        $middleware = $this->createCsrfMiddlewareWithToken($token);
        $response = $middleware->process(
            $this->createPostServerRequestWithHeaderToken($token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetIsAlwaysAllowed(): void
    {
        $middleware = $this->createCsrfMiddlewareWithToken('');
        $response = $middleware->process($this->createServerRequest(Method::GET), $this->createRequestHandler());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidTokenResultIn422(): void
    {
        $middleware = $this->createCsrfMiddlewareWithToken($this->generateToken());
        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken($this->generateToken()),
            $this->createRequestHandler()
        );
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEmptyTokenInSessionResultIn422(): void
    {
        $middleware = $this->createCsrfMiddlewareWithToken('');
        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken($this->generateToken()),
            $this->createRequestHandler()
        );
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEmptyTokenInRequestResultIn422(): void
    {
        $middleware = $this->createCsrfMiddlewareWithToken(
            $this->generateToken()
        );
        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());
        $this->assertEquals(422, $response->getStatusCode());
    }

    private function createServerRequest(
        string $method = Method::POST,
        array $bodyParams = [],
        array $headParams = []
    ): ServerRequestInterface {
        $request = new ServerRequest($method, '/', $headParams);
        return $request->withParsedBody($bodyParams);
    }

    private function createPostServerRequestWithBodyToken(string $token): ServerRequestInterface
    {
        return $this->createServerRequest(Method::POST, $this->getBodyRequestParamsByToken($token));
    }

    private function createPutServerRequestWithBodyToken(string $token): ServerRequestInterface
    {
        return $this->createServerRequest(Method::PUT, $this->getBodyRequestParamsByToken($token));
    }

    private function createDeleteServerRequestWithBodyToken(string $token): ServerRequestInterface
    {
        return $this->createServerRequest(Method::DELETE, $this->getBodyRequestParamsByToken($token));
    }

    private function createPostServerRequestWithHeaderToken(string $token): ServerRequestInterface
    {
        return $this->createServerRequest(Method::POST, [], [
            CsrfMiddleware::HEADER_NAME => TokenMask::apply($token),
        ]);
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function createStorageMock(string $returnToken): CsrfTokenStorageInterface
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);

        $mock
            ->expects($this->once())
            ->method('get')
            ->willReturn($returnToken);

        return $mock;
    }

    private function createCsrfMiddlewareWithToken(string $token): CsrfMiddleware
    {
        $middleware = new CsrfMiddleware(new Psr17Factory(), $this->createStorageMock($token));

        return $middleware->withParameterName(self::PARAM_NAME);
    }

    private function generateToken(): string
    {
        return Random::string();
    }

    private function getBodyRequestParamsByToken(string $token): array
    {
        return [
            self::PARAM_NAME => TokenMask::apply($token),
        ];
    }
}
