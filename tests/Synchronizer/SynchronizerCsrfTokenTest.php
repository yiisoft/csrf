<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Synchronizer;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Csrf\Tests\Synchronizer\Storage\MockCsrfTokenStorage;

final class SynchronizerCsrfTokenTest extends TestCase
{
    public function testRepeatGetValue(): void
    {
        $csrfToken = new SynchronizerCsrfToken(
            new RandomCsrfTokenGenerator(),
            new MockCsrfTokenStorage(),
        );

        $this->assertSame($csrfToken->getValue(), $csrfToken->getValue());
    }
}
