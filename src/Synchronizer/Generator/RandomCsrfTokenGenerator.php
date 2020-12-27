<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer\Generator;

use Yiisoft\Security\Random;

/**
 * Generates a random token.
 */
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
