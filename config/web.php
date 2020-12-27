<?php

declare(strict_types=1);

/* @var array $params */

use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Stateful\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Stateful\SessionCsrfTokenStorage;
use Yiisoft\Csrf\Stateful\StatefulCsrfToken;
use Yiisoft\Csrf\Stateless\SessionCsrfTokenIdentification;
use Yiisoft\Csrf\Stateless\StatelessCsrfToken;
use Yiisoft\Factory\Definitions\Reference;

return [
    CsrfTokenInterface::class => [
        '__class' => MaskedCsrfToken::class,
        '__construct()' => [
            'token' => Reference::to(StatefulCsrfToken::class),
        ],
    ],

    StatefulCsrfToken::class => [
        '__construct()' => [
            'generator' => Reference::to(RandomCsrfTokenGenerator::class),
            'storage' => Reference::to(SessionCsrfTokenStorage::class),
        ],
    ],

    StatelessCsrfToken::class => [
        '__construct()' => [
            'identification' => Reference::to(SessionCsrfTokenIdentification::class),
            'secretKey' => $params['yiisoft/csrf']['statelessToken']['secretKey'],
            'algorithm' => $params['yiisoft/csrf']['statelessToken']['algorithm'],
            'lifetime' => $params['yiisoft/csrf']['statelessToken']['lifetime'],
        ],
    ],
];
