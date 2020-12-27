<?php

declare(strict_types=1);

use Yiisoft\Csrf\Stateful\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Stateful\SessionCsrfTokenStorage;
use Yiisoft\Csrf\Stateless\SessionCsrfTokenIdentification;
use Yiisoft\Factory\Definitions\Reference;

return [
    'yiisoft/csrf' => [
        // Stateful
        'tokenGenerator' => Reference::to(RandomCsrfTokenGenerator::class),
        'tokenStorage' => Reference::to(SessionCsrfTokenStorage::class),

        // Stateless
        'tokenIdentification' => Reference::to(SessionCsrfTokenIdentification::class),
    ],
];
