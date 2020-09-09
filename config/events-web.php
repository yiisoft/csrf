<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Yii\Web\Event\BeforeRequest;

return [
    BeforeRequest::class => [
        fn (BeforeRequest $event, ContainerInterface $container) => CsrfToken::initialize(),
    ],
];
