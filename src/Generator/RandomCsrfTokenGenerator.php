<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Generator;

use Yiisoft\Security\Random;

final class RandomCsrfTokenGenerator implements CsrfTokenGeneratorInterface
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
