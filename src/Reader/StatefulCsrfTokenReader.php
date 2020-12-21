<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Reader;

use Yiisoft\Csrf\Generator\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\Storage\CsrfTokenStorageInterface;

final class StatefulCsrfTokenReader implements CsrfTokenReaderInterface
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
}
