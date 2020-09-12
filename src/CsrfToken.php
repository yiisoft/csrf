<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;
use Yiisoft\Csrf\TokenMask\CsrfTokenMaskInterface;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;

final class CsrfToken
{

    private CsrfTokenStorageInterface $storage;
    private CsrfTokenMaskInterface $tokenMask;

    public function __construct(
        CsrfTokenStorageInterface $storage,
        CsrfTokenMaskInterface $tokenMask
    ) {
        $this->storage = $storage;
        $this->tokenMask = $tokenMask;
    }

    /**
     * @return string
     * @throws LogicException when CSRF token is not defined
     */
    public function getValue(): string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            throw new LogicException('CSRF token is not defined.');
        }
        return $this->tokenMask->apply($token);
    }
}
