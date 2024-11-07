<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii CSRF Protection Library</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/csrf/v)](https://packagist.org/packages/yiisoft/csrf)
[![Total Downloads](https://poser.pugx.org/yiisoft/csrf/downloads)](https://packagist.org/packages/yiisoft/csrf)
[![Build status](https://github.com/yiisoft/csrf/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/csrf/actions/workflows/build.yml)
[![Code coverage](https://codecov.io/gh/yiisoft/csrf/graph/badge.svg?token=APV7NMIAB1)](https://codecov.io/gh/yiisoft/csrf)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fcsrf%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/csrf/master)
[![static analysis](https://github.com/yiisoft/csrf/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/csrf/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/csrf/coverage.svg)](https://shepherd.dev/github/yiisoft/csrf)

The package provides [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware for CSRF protection:

- It supports two algorithms out of the box:
  - Synchronizer CSRF token with customizable token generation and storage. By default, it uses random data and session.
  - HMAC based token with customizable identity generation. Uses session by default.
- It has ability to apply masking to CSRF token string to make [BREACH attack](https://breachattack.com/) impossible.

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/csrf
```

## General usage

In order to enable CSRF protection you need to add `CsrfTokenMiddleware` to your main middleware stack.
In Yii it is done by configuring `config/web/application.php`:

```php
return [
    Yiisoft\Yii\Http\Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to(static function (Injector $injector) {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares(
                        [
                            ErrorCatcher::class,
                            SessionMiddleware::class,
                            CsrfTokenMiddleware::class, // <-- add this
                            Router::class,
                        ]
                    );
            }),
        ],
    ],
];
```

By default, CSRF token is obtained from `_csrf` request body parameter or `X-CSRF-Token` header.

You can access currently valid token as a string using `CsrfTokenInterface`:

```php
/** @var Yiisoft\Csrf\CsrfTokenInterface $csrfToken */
$csrf = $csrfToken->getValue();
```

If the token does not pass validation, the response `422 Unprocessable Entity` will be returned.
You can change this behavior by implementing your own request handler:

```php
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Csrf\CsrfTokenMiddleware;

/**
 * @var Psr\Http\Message\ResponseFactoryInterface $responseFactory
 * @var Yiisoft\Csrf\CsrfTokenInterface $csrfToken
 */
 
$failureHandler = new class ($responseFactory) implements RequestHandlerInterface {
    private ResponseFactoryInterface $responseFactory;
    
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(400);
        $response
            ->getBody()
            ->write('Bad request.');
        return $response;
    }
};

$middleware = new CsrfTokenMiddleware($responseFactory, $csrfToken, $failureHandler);
```

## CSRF Tokens

In case Yii framework is used along with config plugin, the package is [configured](./config/web.php)
automatically to use synchronizer token and masked decorator. You can change that depending on your needs.

### Synchronizer CSRF token

Synchronizer CSRF token is a stateful CSRF token that is a unique random string. It is saved in persistent storage
available only to the currently logged-in user. The same token is added to a form. When the form is submitted,
token that came from the form is compared against the token stored.

`SynchronizerCsrfToken` requires implementation of the following interfaces:

- `CsrfTokenGeneratorInterface` for generating a new CSRF token;
- `CsrfTokenStorageInterface` for persisting a token between requests.

Package provides `RandomCsrfTokenGenerator` that generates a random token and
`SessionCsrfTokenStorage` that persists a token between requests in a user session.

To learn more about the synchronizer token pattern,
[check OWASP CSRF cheat sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern).

### HMAC based token

HMAC based token is a stateless CSRF token that does not require any storage. The token is a hash from session ID and
a timestamp used to prevent replay attacks. The token is added to a form. When the form is submitted, we re-generate
the token from the current session ID and a timestamp from the original token. If two hashes match, we check that the
timestamp is less than the token lifetime.

`HmacCsrfToken` requires implementation of `CsrfTokenIdentityGeneratorInterface` for generating an identity.
The package provides `SessionCsrfTokenIdentityGenerator` that is using session ID thus making the session a token scope.

Parameters set via the `HmacCsrfToken` constructor are:

- `$secretKey` — shared secret key used to generate the hash;
- `$algorithm` — hash algorithm for message authentication. `sha256`, `sha384` or `sha512` are recommended;
- `$lifetime` — number of seconds that the token is valid for.

To learn more about HMAC based token pattern
[check OWASP CSRF cheat sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#hmac-based-token-pattern).

### Stub CSRF token

The `StubCsrfToken` simply stores and returns a token string. It does not perform any additional validation.
This implementation can be useful when mocking CSRF token behavior during unit testing or when providing
placeholder functionality in temporary solutions.

### Masked CSRF token

`MaskedCsrfToken` is a decorator for `CsrfTokenInterface` that applies masking to a token string.
It makes [BREACH attack](https://breachattack.com/) impossible, so it is safe to use token in HTML to be later passed to
the next request either as a hidden form field or via JavaScript async request.

It is recommended to always use this decorator.

## Documentation

- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii CSRF Protection Library is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
