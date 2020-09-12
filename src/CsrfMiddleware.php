<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\TokenMask\CsrfTokenMaskInterface;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Security\Random;

final class CsrfMiddleware implements MiddlewareInterface
{

    public const PARAMETER_NAME = '_csrf';
    public const HEADER_NAME = 'X-CSRF-Token';

    private string $parameterName = self::PARAMETER_NAME;
    private string $headerName = self::HEADER_NAME;

    private ResponseFactoryInterface $responseFactory;
    private CsrfTokenStorageInterface $storage;
    private CsrfTokenMaskInterface $tokenMask;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CsrfTokenStorageInterface $storage,
        CsrfTokenMaskInterface $tokenMask
    ) {
        $this->responseFactory = $responseFactory;
        $this->storage = $storage;
        $this->tokenMask = $tokenMask;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->getToken();

        if (!$this->validateCsrfToken($request, $token)) {
            $this->storage->remove();

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

    private function getToken(): string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            $token = Random::string();
            $this->storage->set($token);
        }

        return $token;
    }

    private function validateCsrfToken(ServerRequestInterface $request, string $trueToken): bool
    {
        $method = $request->getMethod();

        if (\in_array($method, [Method::GET, Method::HEAD, Method::OPTIONS], true)) {
            return true;
        }

        $unmaskedToken = $this->getTokenFromRequest($request);

        return !empty($unmaskedToken) && hash_equals($unmaskedToken, $trueToken);
    }

    private function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();

        $token = $parsedBody[$this->parameterName] ?? null;
        if (empty($token)) {
            $headers = $request->getHeader($this->headerName);
            $token = \reset($headers);
        }

        return is_string($token) ? $this->tokenMask->remove($token) : null;
    }
}
