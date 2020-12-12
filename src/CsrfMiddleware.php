<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\TokenFetcher\CsrfTokenFetcherInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Security\TokenMask;

use function in_array;

final class CsrfMiddleware implements MiddlewareInterface
{
    public const PARAMETER_NAME = '_csrf';
    public const HEADER_NAME = 'X-CSRF-Token';

    private string $parameterName = self::PARAMETER_NAME;
    private string $headerName = self::HEADER_NAME;

    private ResponseFactoryInterface $responseFactory;
    private CsrfTokenFetcherInterface $tokenFetcher;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CsrfTokenFetcherInterface $tokenFetcher
    ) {
        $this->responseFactory = $responseFactory;
        $this->tokenFetcher = $tokenFetcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->validateCsrfToken($request)) {
            $response = $this->responseFactory->createResponse(Status::UNPROCESSABLE_ENTITY);
            $response->getBody()->write(Status::TEXTS[Status::UNPROCESSABLE_ENTITY]);
            return $response;
        }

        return $handler->handle($request);
    }

    public function withParameterName(string $name): self
    {
        $new = clone $this;
        $new->parameterName = $name;
        return $new;
    }

    public function withHeaderName(string $name): self
    {
        $new = clone $this;
        $new->headerName = $name;
        return $new;
    }

    private function validateCsrfToken(ServerRequestInterface $request): bool
    {
        $method = $request->getMethod();

        if (in_array($method, [Method::GET, Method::HEAD, Method::OPTIONS], true)) {
            return true;
        }

        $token = $this->getTokenFromRequest($request);

        return !empty($token) &&
            hash_equals($this->tokenFetcher->getValue(), $token);
    }

    private function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();

        $token = $parsedBody[$this->parameterName] ?? null;
        if (empty($token)) {
            $headers = $request->getHeader($this->headerName);
            $token = reset($headers);
        }

        return is_string($token) ? TokenMask::remove($token) : null;
    }
}
