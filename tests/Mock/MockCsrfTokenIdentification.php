<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests\Mock;

use Yiisoft\Csrf\Stateless\CsrfTokenIdentificationInterface;

final class MockCsrfTokenIdentification implements CsrfTokenIdentificationInterface
{
    private string $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function getString(): string
    {
        return $this->string;
    }
}
