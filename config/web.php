<?php

declare(strict_types=1);

use Yiisoft\Csrf\Stateful\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Stateful\CsrfTokenStorageInterface;
use Yiisoft\Csrf\Stateful\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Stateful\SessionCsrfTokenStorage;
use Yiisoft\Csrf\Stateful\StatefulCsrfToken;
use Yiisoft\Csrf\Stateless\CsrfTokenIdentificationInterface;
use Yiisoft\Csrf\Stateless\SessionCsrfTokenCsrfTokenIdentification;

return [
    CsrfTokenInterface::class => StatefulCsrfToken::class,

    // Stateful
    CsrfTokenGeneratorInterface::class => RandomCsrfTokenGenerator::class,
    CsrfTokenStorageInterface::class => SessionCsrfTokenStorage::class,

    // Stateless
    CsrfTokenIdentificationInterface::class => SessionCsrfTokenCsrfTokenIdentification::class,
];
