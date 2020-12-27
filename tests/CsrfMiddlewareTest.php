<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\Synchronizer\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Csrf\Tests\Synchronizer\MockCsrfTokenStorage;

final class CsrfMiddlewareTest extends TestCase
{
    public function testImmutability(): void
    {
        $original = new CsrfMiddleware(
            new Psr17Factory(),
            new SynchronizerCsrfToken(
                new RandomCsrfTokenGenerator(),
                new MockCsrfTokenStorage()
            )
        );

        $this->assertNotSame($original, $original->withHeaderName('csrf'));
        $this->assertNotSame($original, $original->withParameterName('csrf'));
    }
}
