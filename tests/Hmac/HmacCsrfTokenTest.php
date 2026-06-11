<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;
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
            'mySecretKey',
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
    }

    public function testTokenValueChanges(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
        );

        $this->assertNotSame($csrfToken->getValue(), $csrfToken->getValue());
    }

    public function testTokenDoesNotExposeIdentity(): void
    {
        $identity = 'session-id-that-must-not-be-in-token';
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator($identity),
            'mySecretKey',
        );

        $token = $csrfToken->getValue();

        $this->assertStringNotContainsString($identity, StringHelper::base64UrlDecode($token));
        $this->assertTrue($csrfToken->validate($token));
    }

    public function testTokenPayloadContainsExpirationAndRandomValue(): void
    {
        self::$timeResult = 300;

        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
            'sha256',
            100,
        );

        $payload = StringHelper::base64UrlDecode($csrfToken->getValue());
        $message = StringHelper::byteSubstring($payload, $this->getHashLength(), null);

        $this->assertMatchesRegularExpression('/^400~[A-Za-z0-9_-]{32}$/', $message);
    }

    public function testExpiration(): void
    {
        self::$timeResult = 300;

        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
            'sha256',
            100,
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
            'mySecretKey',
        );

        $this->assertFalse($csrfToken->validate(Random::string()));
        $this->assertFalse($csrfToken->validate('*'));

        $token = StringHelper::base64UrlEncode(
            (new Mac('sha256'))->sign('a2~user1', 'mySecretKey', true),
        );
        $this->assertFalse($csrfToken->validate($token));

        $token = StringHelper::base64UrlEncode(
            (new Mac('sha256'))->sign('hello', 'mySecretKey', true),
        );
        $this->assertFalse($csrfToken->validate($token));
    }

    public function testValidatesTokenSignedWithCurrentIdentityAndMessage(): void
    {
        self::$timeResult = 300;

        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
        );

        $this->assertTrue($csrfToken->validate($this->createToken('user7', '500~random-value-with~delimiter')));
    }

    public function testRejectsSignedTokenWithMalformedMessage(): void
    {
        self::$timeResult = 300;

        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user7'),
            'mySecretKey',
        );

        $this->assertFalse($csrfToken->validate($this->createToken('user7', '500')));
        $this->assertFalse($csrfToken->validate($this->createToken('user7', '0500~random-value')));
        $this->assertFalse($csrfToken->validate($this->createToken('user7', 'not-a-timestamp~random-value')));
    }

    public function testInvalidTokenParsingDoesNotGenerateIdentity(): void
    {
        $identityGenerator = new class implements CsrfTokenIdentityGeneratorInterface {
            public int $calls = 0;

            public function generate(): string
            {
                $this->calls++;
                return 'user7';
            }
        };
        $csrfToken = new HmacCsrfToken($identityGenerator, 'mySecretKey');

        $this->assertFalse($csrfToken->validate(StringHelper::base64UrlEncode('short')));
        $this->assertSame(0, $identityGenerator->calls);
    }

    public function testIdentityWithTilda(): void
    {
        $csrfToken = new HmacCsrfToken(
            new MockCsrfTokenIdentityGenerator('user~7'),
            'mySecretKey',
        );

        $token = $csrfToken->getValue();

        $this->assertTrue($csrfToken->validate($token));
    }

    private function createToken(string $identity, string $message): string
    {
        $signedMessage = StringHelper::byteLength($identity) . '~' . $identity . '~' . $message;

        return StringHelper::base64UrlEncode(hash_hmac('sha256', $signedMessage, 'mySecretKey', true) . $message);
    }

    private function getHashLength(): int
    {
        return StringHelper::byteLength(hash_hmac('sha256', '', '', true));
    }
}

namespace Yiisoft\Csrf\Hmac;

use Yiisoft\Csrf\Tests\Hmac\HmacCsrfTokenTest;

function time(): int
{
    return HmacCsrfTokenTest::$timeResult ?? time();
}
