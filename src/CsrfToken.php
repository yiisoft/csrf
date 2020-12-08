<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;
use Yiisoft\Csrf\TokenGenerator\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Security\TokenMask;

final class CsrfToken
{
    private CsrfTokenGeneratorInterface $generator;
    private CsrfTokenStorageInterface $storage;
    private bool $autoGenerate;

    public function __construct(
        CsrfTokenGeneratorInterface $generator,
        CsrfTokenStorageInterface $storage,
        bool $autoGenerate = true
    ) {
        $this->generator = $generator;
        $this->storage = $storage;
        $this->autoGenerate = $autoGenerate;
    }

    /**
     * @throws LogicException when CSRF token is not defined
     *
     * @return string
     */
    public function getValue(): string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            if ($this->autoGenerate) {
                $token = $this->generator->generate();
                $this->storage->set($token);
            } else {
                throw new LogicException('CSRF token is not defined.');
            }
        }
        return TokenMask::apply($token);
    }

    public function validate(string $token): bool
    {
        $trueToken = $this->storage->get();
        return $trueToken !== null && hash_equals($trueToken, TokenMask::remove($token));
    }
}
