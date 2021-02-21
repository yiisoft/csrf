<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii CSRF Protection Library</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/csrf/v/stable.png)](https://packagist.org/packages/yiisoft/csrf)
[![Total Downloads](https://poser.pugx.org/yiisoft/csrf/downloads.png)](https://packagist.org/packages/yiisoft/csrf)
[![Build status](https://github.com/yiisoft/csrf/workflows/build/badge.svg)](https://github.com/yiisoft/csrf/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/csrf/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/csrf/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/csrf/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/csrf/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fcsrf%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/csrf/master)
[![static analysis](https://github.com/yiisoft/csrf/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/csrf/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/csrf/coverage.svg)](https://shepherd.dev/github/yiisoft/csrf)

The package provides:

- PSR-15 implementation middleware for CSRF protection;
- synchronizer CSRF token that is a unique random string;
- HMAC based token that does not require any storage;
- masked CSRF token applies masking to a token string.

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with composer:

```shell
composer require yiisoft/csrf --prefer-dist
```

## General usage

In order to enable CSRF protection you need to add `CsrfMiddleware` to your main middleware stack. In Yii it is done by configuring `config/web/application.php`:

```php
return [
    Yiisoft\Yii\Web\Application::class => [
        '__construct()' => [
            'dispatcher' => static function (Injector $injector) {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares(
                        [
                            Router::class,
                            CsrfMiddleware::class, // <-- this
                            SessionMiddleware::class,
                            ErrorCatcher::class,
                        ]
                    );
            },
        ],
    ],
];
```

By default, CSRF token getting from parameter `_csrf` or header `X-CSRF-Token` provided in the request body.

You can access to currently valid token as string throught `CsrfTokenInterface`:

```php
/** @var \Yiisoft\Csrf\CsrfTokenInterface $csrfToken */
$csrf = $csrfToken->getValue();
```

## CSRF Tokens

### Synchronizer CSRF token

Stateful CSRF token that is a unique random string. It is stored it in persistent storage available only for the currently logged in user. The same token is added to forms. When the form is submitted, token that came from the form is compared against the token stored.

`SynchronizerCsrfToken` required implementation of interfaces:

- `CsrfTokenGeneratorInterface` for generates a new CSRF token;
- `CsrfTokenStorageInterface` for persists a token between requests.

Package provides `RandomCsrfTokenGenerator` (generates a random token) and
`SessionCsrfTokenStorage` (persists a token between requests in a user session).

See more info about synchronizer token pattern
[here](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern).

### HMAC based token

Stateless CSRF token that does not require any storage. The token is a hash from session ID and a timestamp
(to prevent replay attacks). It is added to forms. When the form is submitted, we re-generate the token from the current session ID and a timestamp from the original token. If two hashes match, we check that timestamp is less than setted.

`HmacCsrfToken` required implementation `CsrfTokenIdentityGeneratorInterface` for generate identity. Package provides `SessionCsrfTokenIdentityGenerator` using session ID makes the session a token scope.

Parameters set via the `HmacCsrfToken` constructor:

- `$secretKey` — shared secret key used to generate the hash;
- `$algorithm` — hash algorithm for message authentication, recommend `sha256`, `sha384` or `sha512`;
- `$lifetime` — number of seconds that the token is valid for.

See more info about HMAC based token pattern
[here](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#hmac-based-token-pattern).

### Masked CSRF token

`MaskedCsrfToken` is decorator for `CsrfTokenInterface` applies masking to a token string. It makes BREACH attack impossible so it is safe to use it in HTML to be later passed to the next request either as a hidden form field or via JavaScript async request.

It is recommended to always use this decorator.

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii CSRF Protection Library is free software. It is released under the terms of the BSD License. Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
