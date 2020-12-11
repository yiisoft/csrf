<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Token;

use Yiisoft\Security\Random;

final class RandomCsrfToken implements StateCsrfTokenInterface
{
    private int $length;

    public function __construct(int $length = 32)
    {
        $this->length = $length;
    }

    public function generate(): string
    {
        return Random::string($this->length);
    }
}
