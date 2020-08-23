<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use Yiisoft\Csrf\CsrfToken;

final class CsrfTokenTest extends TestCase
{

    public function testBase(): void
    {
        CsrfToken::setValue('test_token');
        $this->assertSame('test_token', CsrfToken::getValue());
    }

    public function testRepeatedSet(): void
    {
        CsrfToken::setValue('test_token');
        $this->expectExceptionMessage('The CSRF token is already set.');
        CsrfToken::setValue('test_token');
    }

    public function testEarlyGet(): void
    {
        $this->assertNull(CsrfToken::getValue());
    }
}
