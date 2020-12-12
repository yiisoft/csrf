<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenFetcher;

use Yiisoft\Csrf\TokenGenerator\CsrfTokenGeneratorInterface;

final class StatelessCsrfTokenFetcher
{
    private CsrfTokenGeneratorInterface $generator;

    public function __construct(CsrfTokenGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function getValue(): string
    {
        return $this->generator->generate();
    }
}
