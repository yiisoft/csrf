<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\StubCsrfToken;

final class StubCsrfTokenTest extends TestCase
{
    public function testValue(): void
    {
        $stubToken = new StubCsrfToken('test');
        $this->assertSame('test', $stubToken->getValue());
    }

    public function testValidate(): void
    {
        $stubToken = new StubCsrfToken('test');
        $this->assertTrue($stubToken->validate('test'));
        $this->assertFalse($stubToken->validate('other'));
    }

    public function testEmptyToken(): void
    {
        $stubToken = new StubCsrfToken();
        $token = $stubToken->getValue();
        $this->assertNotEmpty($token);
        $this->assertTrue($stubToken->validate($token));
    }
}
