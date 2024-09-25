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

use function count;
use function in_array;

/**
 * PSR-15 middleware that takes care of HTTP header validation.
 *
 * @link https://www.php-fig.org/psr/psr-15/
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#employing-custom-request-headers-for-ajaxapi
 */
final class CsrfHeaderMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME = 'X-CSRF-Token';

    private string $headerName = self::HEADER_NAME;
    private array $safeMethods = [Method::OPTIONS];

    private ResponseFactoryInterface $responseFactory;
    private ?RequestHandlerInterface $failureHandler;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        RequestHandlerInterface $failureHandler = null
    ) {
        $this->responseFactory = $responseFactory;
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

    public function withHeaderName(string $name): self
    {
        $new = clone $this;
        $new->headerName = $name;
        return $new;
    }

    public function withSafeMethods(array $methods): self
    {
        $new = clone $this;
        $new->safeMethods = $methods;
        return $new;
    }

    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    public function getSafeMethods(): array
    {
        return $this->safeMethods;
    }

    private function validateCsrfToken(ServerRequestInterface $request): bool
    {
        if (in_array($request->getMethod(), $this->safeMethods, true)) {
            return true;
        }

        $headers = $request->getHeader($this->headerName);
        return (bool) count($headers);
    }
}
