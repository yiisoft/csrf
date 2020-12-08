<?php

declare(strict_types=1);

use Yiisoft\Csrf\TokenGenerator\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\TokenGenerator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Csrf\TokenStorage\SessionCsrfTokenStorage;

return [
    CsrfTokenGeneratorInterface::class => RandomCsrfTokenGenerator::class,
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,
];
