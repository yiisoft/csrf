<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac\IdentityGenerator;

use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;

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
