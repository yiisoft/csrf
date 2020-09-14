<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\TokenStorage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;
use Yiisoft\Session\SessionInterface;

class SessionCsrfTokenStorageTest extends TestCase
{

    public function testGet()
    {
        /** @var SessionInterface|MockObject $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->willReturn('token');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $this->assertSame('token', $storage->get());
    }

    public function testSet()
    {
        /** @var SessionInterface|MockObject $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('set');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $storage->set('token');
    }

    public function testRemove()
    {
        /** @var SessionInterface|MockObject $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('remove');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $storage->remove();
    }
}
