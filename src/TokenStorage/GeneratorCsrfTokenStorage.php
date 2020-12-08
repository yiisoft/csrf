<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenStorage;

use Yiisoft\Csrf\TokenGenerator\CsrfTokenGeneratorInterface;

final class GeneratorCsrfTokenStorage implements CsrfTokenStorageInterface
{
    private CsrfTokenGeneratorInterface $generator;

    public function __construct(CsrfTokenGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function get(): string
    {
        return $this->generator->generate();
    }

    public function set(string $token): void
    {
    }

    public function remove(): void
    {
    }
}
