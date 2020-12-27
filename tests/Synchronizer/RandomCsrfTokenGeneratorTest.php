<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Synchronizer;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Synchronizer\RandomCsrfTokenGenerator;

final class RandomCsrfTokenGeneratorTest extends TestCase
{
    public function testBase(): void
    {
        $generator = new RandomCsrfTokenGenerator();

        $this->assertSame(32, strlen($generator->generate()));
    }
}
