<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Mock;

use Yiisoft\Csrf\Stateful\CsrfTokenStorageInterface;

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
