<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\TokenMask;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\TokenMask\CsrfTokenMask;

final class
CsrfTokenMaskTest extends TestCase
{

    public function testBase(): void
    {
        $tokenMask = new CsrfTokenMask();

        $masked = $tokenMask->apply('token');

        $this->assertSame('token', $tokenMask->remove($masked));
    }
}
