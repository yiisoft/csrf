<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer\Generator;

use Yiisoft\Security\Random;

/**
 * Generates a random token.
 */
final class RandomCsrfTokenGenerator implements CsrfTokenGeneratorInterface
{
    public function __construct(private int $length = 32)
    {
    }

    public function generate(): string
    {
        return Random::string($this->length);
    }
}
