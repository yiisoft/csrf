<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Hmac\SessionCsrfTokenIdentityGenerator;
use Yiisoft\Session\SessionInterface;

final class SessionCsrfTokenIdentityGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        /** @var MockObject|SessionInterface $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('42');

        $identityGenerator = new SessionCsrfTokenIdentityGenerator($sessionMock);

        $this->assertSame('42', $identityGenerator->generate());
    }
}
