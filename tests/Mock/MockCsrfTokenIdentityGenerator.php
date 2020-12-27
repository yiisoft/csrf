<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Mock;

use Yiisoft\Csrf\Hmac\CsrfTokenIdentityGeneratorInterface;

final class MockCsrfTokenIdentityGenerator implements CsrfTokenIdentityGeneratorInterface
{
    private string $identity;

    public function __construct(string $identity)
    {
        $this->identity = $identity;
    }

    public function generate(): string
    {
        return $this->identity;
    }
}
