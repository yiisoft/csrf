<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Hmac\IdentityGenerator;

use Yiisoft\Csrf\Hmac\IdentityGenerator\CsrfTokenIdentityGeneratorInterface;

final class MockCsrfTokenIdentityGenerator implements CsrfTokenIdentityGeneratorInterface
{
    public function __construct(private string $identity)
    {
    }

    public function generate(): string
    {
        return $this->identity;
    }
}
