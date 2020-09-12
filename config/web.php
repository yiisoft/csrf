<?php

declare(strict_types=1);

use Yiisoft\Csrf\TokenMaskService\TokenMaskServiceInterface;
use Yiisoft\Csrf\TokenMaskService\TokenMaskService;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;

return [
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
    TokenMaskServiceInterface::class => TokenMaskService::class,
];
