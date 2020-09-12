<?php

declare(strict_types=1);

use Yiisoft\Csrf\TokenMask\CsrfTokenMaskInterface;
use Yiisoft\Csrf\TokenMask\CsrfTokenMask;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;

return [
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
    CsrfTokenMaskInterface::class => CsrfTokenMask::class,
];
