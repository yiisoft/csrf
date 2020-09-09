<?php

declare(strict_types=1);

use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Yii\Web\Event\BeforeRequest;

return [
    BeforeRequest::class => [
        fn () => CsrfToken::initialize(),
    ],
];
