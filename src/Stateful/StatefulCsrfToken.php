<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateful;

use Yiisoft\Csrf\CsrfTokenInterface;

final class StatefulCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenGeneratorInterface $generator;
    private CsrfTokenStorageInterface $storage;

    public function __construct(
        CsrfTokenGeneratorInterface $generator,
        CsrfTokenStorageInterface $storage
    ) {
        $this->generator = $generator;
        $this->storage = $storage;
    }

    public function getValue(): string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            $token = $this->generator->generate();
            $this->storage->set($token);
        }

        return $token;
    }

    public function validate(string $token): bool
    {
        return hash_equals($this->getValue(), $token);
    }
}
