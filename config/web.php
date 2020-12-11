<?php

declare(strict_types=1);

use Yiisoft\Csrf\Token\CsrfTokenInterface;
use Yiisoft\Csrf\Token\RandomCsrfToken;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;

return [
    CsrfTokenInterface::class => RandomCsrfToken::class,
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
];
