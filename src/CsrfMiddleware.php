<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Security\Random;
use Yiisoft\Security\TokenMask;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const NAME = '_csrf';
    public const HEADER_NAME = 'X-CSRF-Token';

    private string $name = self::NAME;
    private ResponseFactoryInterface $responseFactory;
    private CsrfTokenStorageInterface $storage;
    private CsrfToken $csrfToken;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CsrfTokenStorageInterface $storage,
        CsrfToken $csrfToken
    ) {
        $this->responseFactory = $responseFactory;
        $this->storage = $storage;
        $this->csrfToken = $csrfToken;
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

        $this->csrfToken->setValue(TokenMask::apply($token));

        return $handler->handle($request);
    }

    public function withName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    private function getToken(): ?string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            $token = Random::string();
            $this->storage->set($token);
        }

        return $token;
    }

    private function validateCsrfToken(ServerRequestInterface $request, ?string $trueToken): bool
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

        $token = $parsedBody[$this->name] ?? null;
        if (empty($token)) {
            $headers = $request->getHeader(self::HEADER_NAME);
            $token = \reset($headers);
        }

        return is_string($token) ? TokenMask::remove($token) : null;
    }
}
