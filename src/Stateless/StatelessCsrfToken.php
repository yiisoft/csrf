<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

use Yiisoft\Csrf\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\CsrfTokenInterface;

final class StatelessCsrfToken implements CsrfTokenInterface
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
