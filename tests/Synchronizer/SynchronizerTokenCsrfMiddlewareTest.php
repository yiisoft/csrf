<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Synchronizer;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Synchronizer\Storage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Csrf\Tests\Synchronizer\Storage\MockCsrfTokenStorage;
use Yiisoft\Csrf\Tests\TokenCsrfMiddlewareTest;
use Yiisoft\Security\Random;

final class SynchronizerTokenCsrfMiddlewareTest extends TokenCsrfMiddlewareTest
{
    public function testEmptyTokenInSessionResultIn422(): void
    {
        $middleware = $this->createCsrfMiddleware(
            new SynchronizerCsrfToken(
                new RandomCsrfTokenGenerator(),
                new MockCsrfTokenStorage()
            )
        );

        $response = $middleware->process(
            $this->createPostServerRequestWithBodyToken(Random::string()),
            $this->createRequestHandler()
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    protected function createCsrfToken(): CsrfTokenInterface
    {
        return new SynchronizerCsrfToken(
            new RandomCsrfTokenGenerator(),
            $this->createStorageMock(Random::string())
        );
    }

    private function createStorageMock(string $returnToken): CsrfTokenStorageInterface
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);

        $mock
            ->method('get')
            ->willReturn($returnToken);

        return $mock;
    }
}
