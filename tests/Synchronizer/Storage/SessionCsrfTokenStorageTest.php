<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Synchronizer\Storage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\Synchronizer\Storage\SessionCsrfTokenStorage;
use Yiisoft\Session\SessionInterface;

final class SessionCsrfTokenStorageTest extends TestCase
{
    public function testGet(): void
    {
        /** @var MockObject|SessionInterface $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->willReturn('token');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $this->assertSame('token', $storage->get());
    }

    public function testSet(): void
    {
        /** @var MockObject|SessionInterface $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('set');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $storage->set('token');
    }

    public function testRemove(): void
    {
        /** @var MockObject|SessionInterface $sessionMock */
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('remove');

        $storage = new SessionCsrfTokenStorage($sessionMock);

        $storage->remove();
    }
}
