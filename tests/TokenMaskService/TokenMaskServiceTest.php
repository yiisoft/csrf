<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\TokenMaskService;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\TokenMaskService\TokenMaskService;

final class TokenMaskServiceTest extends TestCase
{

    public function testBase(): void
    {
        $service = new TokenMaskService();

        $masked = $service->apply('token');

        $this->assertSame('token', $service->remove($masked));
    }
}
