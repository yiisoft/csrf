<?php

declare(strict_types=1);

use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Yii\Web\Event\BeforeRequest;

return [
    BeforeRequest::class => [
        // Initialize static class CsrfToken for reset token when use an alternative way of running
        // an application. For example, RoadRunner or Swoole.
        fn () => CsrfToken::initialize(),
    ],
];
