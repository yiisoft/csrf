<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Csrf\CsrfToken;

return [
    \Yiisoft\Yii\Web\Event\BeforeRequest::class => [
        fn (object $event, ContainerInterface $container) => CsrfToken::initialize(),
    ],
];
