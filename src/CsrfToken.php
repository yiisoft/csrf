<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;
use Yiisoft\Csrf\TokenMaskService\TokenMaskServiceInterface;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;

final class CsrfToken
{

    private CsrfTokenStorageInterface $storage;
    private TokenMaskServiceInterface $tokenMaskService;

    public function __construct(
        CsrfTokenStorageInterface $storage,
        TokenMaskServiceInterface $tokenMaskService
    ) {
        $this->storage = $storage;
        $this->tokenMaskService = $tokenMaskService;
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
        return $this->tokenMaskService->apply($token);
    }
}
