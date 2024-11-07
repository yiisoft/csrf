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

use function in_array;
use function is_string;

/**
 * PSR-15 middleware that takes care of token validation.
 *
 * @link https://www.php-fig.org/psr/psr-15/
 */
final class CsrfTokenMiddleware implements MiddlewareInterface
{
    public const PARAMETER_NAME = '_csrf';
    public const HEADER_NAME = 'X-CSRF-Token';

    private string $parameterName = self::PARAMETER_NAME;
    private string $headerName = self::HEADER_NAME;

    private ResponseFactoryInterface $responseFactory;
    private CsrfTokenInterface $token;
    private ?RequestHandlerInterface $failureHandler;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        CsrfTokenInterface $token,
        RequestHandlerInterface $failureHandler = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->token = $token;
        $this->failureHandler = $failureHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->validateCsrfToken($request)) {
            return $handler->handle($request);
        }

        if ($this->failureHandler !== null) {
            return $this->failureHandler->handle($request);
        }

        $response = $this->responseFactory->createResponse(Status::UNPROCESSABLE_ENTITY);
        $response
            ->getBody()
            ->write(Status::TEXTS[Status::UNPROCESSABLE_ENTITY]);
        return $response;
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

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    private function validateCsrfToken(ServerRequestInterface $request): bool
    {
        if (in_array($request->getMethod(), [Method::GET, Method::HEAD, Method::OPTIONS], true)) {
            return true;
        }

        $token = $this->getTokenFromRequest($request);

        return !empty($token) && $this->token->validate($token);
    }

    private function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();

        $token = $parsedBody[$this->parameterName] ?? null;
        if (empty($token)) {
            $headers = $request->getHeader($this->headerName);
            $token = reset($headers);
        }

        return is_string($token) ? $token : null;
    }
}
