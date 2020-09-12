<?php

declare(strict_types=1);

use Yiisoft\Csrf\Token\CsrfTokenInterface;
use Yiisoft\Csrf\Token\CsrfToken;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;

return [
    CsrfTokenInterface::class => CsrfToken::class,
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
];
