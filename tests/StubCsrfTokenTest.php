<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\StubCsrfToken;
use Yiisoft\Security\Random;

final class StubCsrfTokenTest extends TestCase
{
    public function testValue(): void
    {
        $csrfToken = Random::string();
        $stubToken = new StubCsrfToken($csrfToken);
        $this->assertSame($csrfToken, $stubToken->getValue());
    }

    public function testValidate(): void
    {
        $csrfToken = Random::string();
        $stubToken = new StubCsrfToken($csrfToken);
        $this->assertTrue($stubToken->validate($csrfToken));
        $this->assertFalse($stubToken->validate(Random::string()));
    }

    public function testEmptyToken(): void
    {
        $stubToken = new StubCsrfToken();
        $token = $stubToken->getValue();
        $this->assertNotEmpty($token);
        $this->assertTrue($stubToken->validate($token));
    }
}
