<?php

declare(strict_types=1);

namespace Yiisoft\csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\csrf\CsrfToken;

class CsrfTokenTest extends TestCase
{

    private CsrfToken $csrfToken;

    protected function setUp(): void
    {
        $this->csrfToken = new CsrfToken();
    }

    public function testBase(): void
    {
        $this->csrfToken->setToken('test_token');
        $this->assertSame('test_token', $this->csrfToken->getToken());
    }

    public function testRepeatedSet(): void
    {
        $this->csrfToken->setToken('test_token');
        $this->expectExceptionMessage('The CSRF token is already set.');
        $this->csrfToken->setToken('test_token');
    }

    public function testEarlyGet(): void
    {
        $this->expectExceptionMessage('The CSRF token is not set.');
        $this->csrfToken->getToken();
    }
}
