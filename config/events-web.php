<?php

declare(strict_types=1);

use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Yii\Web\Event\BeforeRequest;

return [
    BeforeRequest::class => [
        // Resets the token. It is relevant when requests are handled in a loop.
        // I. e. with RoadRunner, Swoole, Codeception.
        fn () => CsrfToken::initialize(),
    ],
];
