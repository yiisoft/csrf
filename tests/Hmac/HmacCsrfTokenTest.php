<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\Tests\Hmac\IdentityGenerator\MockCsrfTokenIdentityGenerator;
use Yiisoft\Security\Mac;
use Yiisoft\Security\Random;
use Yiisoft\Strings\StringHelper;

final class HmacCsrfTokenTest extends TestCase
{
    /**
     * Use different values in different tests
     *
     * @var int|null
     */
    public static ?int $timeResult;

    protected function setUp(): void
    {
        self::$timeResult = null;
        parent::setUp();
    }

    public function testBase(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey'
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
    }

    public function testExpiration(): void
    {
        self::$timeResult = 300;

        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
            'sha256',
            100
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));

        self::$timeResult = 400;
        $this->assertTrue($csrfToken->validate($token));

        self::$timeResult = 900;
        $this->assertFalse($csrfToken->validate($token));
    }

    public function testIncorrectToken(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey'
        );

        $this->assertFalse($csrfToken->validate(Random::string()));

        $token = StringHelper::base64UrlEncode(
            (new Mac('sha256'))->sign('a2~user1', 'mySecretKey', true)
        );
        $this->assertFalse($csrfToken->validate($token));

        $token = StringHelper::base64UrlEncode(
            (new Mac('sha256'))->sign('hello', 'mySecretKey', true)
        );
        $this->assertFalse($csrfToken->validate($token));
    }

    public function testIdentityWithTilda(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user~7'),
            'mySecretKey'
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
    }
}

namespace Yiisoft\Csrf\Hmac;

use Yiisoft\Csrf\Tests\Hmac\HmacCsrfTokenTest;

function time(): int
{
    return HmacCsrfTokenTest::$timeResult ?? \time();
}
