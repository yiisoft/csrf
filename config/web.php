<?php

declare(strict_types=1);

use Yiisoft\Csrf\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Stateful\CsrfTokenStorageInterface;
use Yiisoft\Csrf\Stateful\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Stateful\SessionCsrfTokenStorage;
use Yiisoft\Csrf\Stateful\StatefulCsrfToken;

return [
    CsrfTokenInterface::class => StatefulCsrfToken::class,
    CsrfTokenGeneratorInterface::class => RandomCsrfTokenGenerator::class,
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
];
