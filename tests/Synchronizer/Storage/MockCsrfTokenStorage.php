<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Synchronizer\Storage;

use Yiisoft\Csrf\Synchronizer\Storage\CsrfTokenStorageInterface;

class MockCsrfTokenStorage implements CsrfTokenStorageInterface
{
    private ?string $token = null;

    public function get(): ?string
    {
        return $this->token;
    }

    public function set(string $token): void
    {
        $this->token = $token;
    }

    public function remove(): void
    {
        $this->token = null;
    }
}
