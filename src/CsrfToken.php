<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Security\TokenMask;

final class CsrfToken
{
    private CsrfTokenStorageInterface $storage;

    public function __construct(CsrfTokenStorageInterface $storage)
    {
        $this->storage = $storage;
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
            throw new LogicException('CSRF token is not defined.');
        }
        return TokenMask::apply($token);
    }
}
