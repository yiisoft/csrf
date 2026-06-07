<?php

declare(strict_types=1);

return [
    'yiisoft/csrf' => [
        'hmacToken' => [
            'secretKey' => (string) getenv('YII_CSRF_SECRET_KEY'),
            'algorithm' => 'sha256',
            'lifetime' => 300,
        ],
    ],
];
