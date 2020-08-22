<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfToken;

final class CsrfTokenTest extends TestCase
{

    public function testBase(): void
    {
        CsrfToken::setValue('test_token');
        $this->assertSame('test_token', CsrfToken::getValue());
    }
}
