<?php

declare(strict_types=1);

/* @var array $params */

use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Stateful\StatefulCsrfToken;

return [
    CsrfTokenInterface::class => [
        '__class' => MaskedCsrfToken::class,
        '__construct()' => [
            'token' => static function () use ($params) {
                return new StatefulCsrfToken(
                    $params['yiisoft/csrf']['tokenGenerator'],
                    $params['yiisoft/csrf']['tokenStorage'],
                );
            },
        ],
    ],
];
