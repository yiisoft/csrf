<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Reader;

use Yiisoft\Csrf\Generator\CsrfTokenGeneratorInterface;

final class StatelessCsrfTokenReader implements CsrfTokenReaderInterface
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
