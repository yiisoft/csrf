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
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Security\Random;

abstract class TokenCsrfMiddlewareTest extends TestCase
{
    private const PARAM_NAME = 'csrf';

    private string $token;

    public function testValidTokenInBodyPostRequestResultIn200(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken($this->token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInBodyPutRequestResultIn200(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process(
            $this->createPutServerRequestWithBodyToken($this->token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInBodyDeleteRequestResultIn200(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process(
            $this->createDeleteServerRequestWithBodyToken($this->token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInHeaderResultIn200(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process(
            $this->createPostServerRequestWithHeaderToken($this->token),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidTokenInCustomHeaderResultIn200(): void
    {
        $headerName = 'CUSTOM-CSRF';

        $middleware = $this
            ->createCsrfMiddleware()
            ->withHeaderName($headerName);
        $response = $middleware->process(
            $this->createPostServerRequestWithHeaderToken($this->token, $headerName),
            $this->createRequestHandler()
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetIsAlwaysAllowed(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process($this->createServerRequest(Method::GET), $this->createRequestHandler());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidTokenResultIn422(): void
    {
        $middleware = $this->createCsrfMiddleware();

        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken(Random::string()),
            $this->createRequestHandler()
        );

        $this->assertEquals(Status::TEXTS[Status::UNPROCESSABLE_ENTITY], $response->getBody());
        $this->assertEquals(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testInvalidTokenResultWithCustomFailureHandler(): void
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

        $middleware = $this->createCsrfMiddleware(null, $failureHandler);

        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken(Random::string()),
            $this->createRequestHandler(),
        );

        $this->assertEquals(Status::TEXTS[Status::BAD_REQUEST], $response->getBody());
        $this->assertEquals(Status::BAD_REQUEST, $response->getStatusCode());
    }

    public function testEmptyTokenInRequestResultIn422(): void
    {
        $middleware = $this->createCsrfMiddleware();
        $response = $middleware->process($this->createServerRequest(), $this->createRequestHandler());
        $this->assertEquals(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    private function createServerRequest(
        string $method = Method::POST,
        array $bodyParams = [],
        array $headParams = []
    ): ServerRequestInterface {
        $request = new ServerRequest($method, '/', $headParams);
        return $request->withParsedBody($bodyParams);
    }

    protected function createPostServerRequestWithBodyToken(string $token): ServerRequestInterface
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

    private function createPostServerRequestWithHeaderToken(
        string $token,
        string $headerName = CsrfTokenMiddleware::HEADER_NAME
    ): ServerRequestInterface {
        return $this->createServerRequest(Method::POST, [], [
            $headerName => $token,
        ]);
    }

    protected function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function getBodyRequestParamsByToken(string $token): array
    {
        return [
            self::PARAM_NAME => $token,
        ];
    }

    protected function createCsrfMiddleware(
        ?CsrfTokenInterface $csrfToken = null,
        RequestHandlerInterface $failureHandler = null
    ): CsrfTokenMiddleware {
        $csrfToken = new MaskedCsrfToken($csrfToken ?? $this->createCsrfToken());
        $this->token = $csrfToken->getValue();

        $middleware = new CsrfTokenMiddleware(new Psr17Factory(), $csrfToken, $failureHandler);

        return $middleware->withParameterName(self::PARAM_NAME);
    }

    abstract protected function createCsrfToken(): CsrfTokenInterface;
}
