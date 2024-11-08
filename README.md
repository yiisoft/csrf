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
In Yii it is done by configuring `MiddlewareDispatcher`:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/application.php
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

By default, `CsrfTokenMiddleware` considers `GET`, `HEAD`, `OPTIONS` methods as safe operations and doesn't perform CSRF validation. You can change this behavior as follows:

```php
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Http\Method;

$csrfTokenMiddleware = $container->get(CsrfTokenMiddleware::class);

// Returns a new instance with the specified list of safe methods.
$csrfTokenMiddleware = $csrfTokenMiddleware->withSafeMethods([Method::OPTIONS]);

// Returns a new instance with the specified header name.
$csrfTokenMiddleware = $csrfTokenMiddleware->withHeaderName('X-CSRF-PROTECTION');
```

or define the `CsrfTokenMiddleware` configuration in the DI container:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/csrf-token.php
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Http\Method;

return [
    CsrfTokenMiddleware::class => [
        'withSafeMethods()' => [[Method::OPTIONS]],
        'withHeaderName()' => ['X-CSRF-PROTECTION'],
    ],
];
```

## CSRF Tokens

In case Yii framework is used along with config plugin, the package is [configured](./config/di-web.php)
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

## CSRF protection for AJAX/SPA backend API

If you are using a cookie to authenticate your AJAX/SPA, then you do need CSRF protection for the backend API.

### Employing custom request header

In this pattern, AJAX/SPA frontend appends a custom header to API requests that require CSRF protection. No token is needed for this approach. This defense relies on the CORS preflight mechanism which sends an `OPTIONS` request to verify CORS compliance with the destination server. All modern browsers, according to the same-origin policy security model, designate requests with custom headers as "to be preflighted". When the API requires a custom header, you know that the request must have been preflighted if it came from a browser.

The header can be any arbitrary key-value pair, as long as it does not conflict with existing headers. Empty value is also acceptable.

```
X-CSRF-HEADER=1
```

When handling the request, the API checks for the existence of this header. If the header does not exist, the backend rejects the request as potential forgery. Employing a custom header allows to reject [simple requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#simple_requests) that browsers do not designate as "to be preflighted" and permit them to be sent to any origin.

In order to enable CSRF protection you need to add `CsrfHeaderMiddleware` to your `MiddlewareDispatcher` configuration:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/application.php
return [
    Yiisoft\Yii\Http\Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to(static function (Injector $injector) {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares(
                        [
                            ErrorCatcher::class,
                            CsrfHeaderMiddleware::class, // <-- add this
                            Router::class,
                        ]
                    );
            }),
        ],
    ],
];
```

or to the routes that must be protected:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/router.php
return [
    RouteCollectionInterface::class => static function (RouteCollectorInterface $collector) use ($config) {
        $collector
            ->middleware(CsrfHeaderMiddleware::class) // <-- add this
            ->addGroup(Group::create(null)->routes($routes));

        return new RouteCollection($collector);
    },
];
```

By default, `CsrfHeaderMiddleware` considers only `GET`, `HEAD`, `POST` methods as unsafe operations. Requests with other HTTP methods trigger CORS preflight and do not require CSRF header validation. You can change this behavior as follows:

```php
use Yiisoft\Csrf\CsrfHeaderMiddleware;
use Yiisoft\Http\Method;

$csrfHeaderMiddleware = $container->get(CsrfHeaderMiddleware::class);

// Returns a new instance with the specified list of unsafe methods.
$csrfHeaderMiddleware = $csrfHeaderMiddleware->withUnsafeMethods([Method::POST]);

// Returns a new instance with the specified header name.
$csrfHeaderMiddleware = $csrfHeaderMiddleware->withHeaderName('X-CSRF-PROTECTION');
```

or define the `CsrfHeaderMiddleware` configuration in the DI container:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/csrf-header.php
use Yiisoft\Csrf\CsrfHeaderMiddleware;
use Yiisoft\Http\Method;

return [
    CsrfHeaderMiddleware::class => [
        'withUnsafeMethods()' => [[Method::POST]],
        'withHeaderName()' => ['X-CSRF-PROTECTION'],
    ],
];
```

The use of a custom request header for CSRF protection is based on the CORS Protocol. Thus, you **must** configure the CORS module to allow or deny cross-origin access to the backend API.

>**Warning**  
>
>`CsrfHeaderMiddleware` can be used to prevent forgery of same-origin requests and requests from the list of specific origins only.


### Protecting same-origin requests

In this scenario:

- AJAX/SPA frontend and API backend have the same origin.
- Cross-origin requests to the API server are denied.
- Simple CORS requests must be restricted.

#### Configure CORS module

- Responses to a CORS preflight requests **must not** contain CORS headers.
- Responses to an actual requests **must not** contain CORS headers.

#### Configure middlewares stack

Add `CsrfHeaderMiddleware` to the main middleware stack:

```php
$middlewareDispatcher = $injector->make(MiddlewareDispatcher::class);
$middlewareDispatcher = $middlewareDispatcher->withMiddlewares([
    ErrorCatcher::class,
    CsrfHeaderMiddleware::class, // <-- add this
    Router::class,
]);
```

or to the routes that must be protected:

```php
$collector = $container->get(RouteCollectorInterface::class);
$collector->addGroup(
    Group::create('/api')
        ->middleware(CsrfHeaderMiddleware::class) // <-- add this
        ->routes($routes)
);
```

#### Configure frontend requests

On the frontend add to the `GET`, `HEAD`, `POST` requests a custom header defined in the `CsrfHeaderMiddleware` with an empty or random value.

```js
let response = fetch('https://example.com/api/whoami', {
  headers: {
    "X-CSRF-HEADER": crypto.randomUUID()
  }
});
```

### Protecting requests from the list of specific origins

In this scenario:

- AJAX/SPA frontend and API backend have different origins.
- Allow cross origin requests to the API server from the list of specific origins only.
- Simple CORS requests must be restricted.

#### Configure CORS module

- A successful responses to a CORS preflight requests **must** contain appropriate CORS headers.
- Responses to an actual requests **must** contain appropriate CORS headers.
- Value of the CORS header `Access-Control-Allow-Origin` **must** contains origin from the predefined list.

```
// assuming frontend origin is https://example.com and backend origin is https://api.example.com
Access-Control-Allow-Origin: https://example.com
```

#### Configure middlewares stack

Add `CsrfHeaderMiddleware` to the main middleware stack:

```php
$middlewareDispatcher = $injector->make(MiddlewareDispatcher::class);
$middlewareDispatcher = $middlewareDispatcher->withMiddlewares([
    ErrorCatcher::class,
    CsrfHeaderMiddleware::class, // <-- add this
    Router::class,
]);
```

or to the routes that must be protected:

```php
$collector = $container->get(RouteCollectorInterface::class);
$collector->addGroup(
    Group::create('/api')
        ->middleware(CsrfHeaderMiddleware::class) // <-- add this
        ->routes($routes)
);
```

#### Configure frontend requests

On the frontend add to the `GET`, `HEAD`, `POST` requests a custom header defined in the `CsrfHeaderMiddleware` with an empty or random value.

```js
let response = fetch('https://api.example.com/whoami', {
  headers: {
    "X-CSRF-HEADER": crypto.randomUUID()
  }
});
```

### Protecting requests passed from any origin

In this scenario:

- AJAX/SPA frontend and API backend have different origins.
- Allow cross origin requests to the API server from any origin.
- All requests are considered unsafe and **must** be protected against CSRF with CSRF-token.

#### Configure CORS module

- A successful responses to a CORS preflight requests **must** contain appropriate CORS headers.
- Responses to an actual requests **must** contain appropriate CORS headers.
- The CORS header `Access-Control-Allow-Origin` has the same value as `Origin` header in the request.

```
$frontendOrigin = $request->getOrigin();

Access-Control-Allow-Origin: $frontendOrigin
```

#### Configure middlewares stack

By default, `CsrfTokenMiddleware` considers `GET`, `HEAD`, `OPTIONS` methods as safe operations and doesn't perform CSRF validation.
In JavaScript-based apps, requests are made programmatically; therefore, to increase application protection, the only `OPTIONS` method can be considered safe and need not be appended with a CSRF token header.

Configure `CsrfTokenMiddleware` safe methods:

```php
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Http\Method;

$csrfTokenMiddleware = $container->get(CsrfTokenMiddleware::class);
$csrfTokenMiddleware = $csrfTokenMiddleware->withSafeMethods([Method::OPTIONS]);
```

or in the DI container:

>[yiisoft/di](https://github.com/yiisoft/di) configuration example
```php
// config/web/di/csrf-token.php
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Http\Method;

return [
    CsrfTokenMiddleware::class => [
        'withSafeMethods()' => [[Method::OPTIONS]],
    ],
];
```

Add `CsrfTokenMiddleware` to the main middleware stack:

```php
$middlewareDispatcher = $injector->make(MiddlewareDispatcher::class);
$middlewareDispatcher = $middlewareDispatcher->withMiddlewares([
    ErrorCatcher::class,
    SessionMiddleware::class,
    CsrfTokenMiddleware::class, // <-- add this
    Router::class,
]);
```

or to the routes that must be protected:

```php
$collector = $container->get(RouteCollectorInterface::class);
$collector->addGroup(
    Group::create('/api')
        ->middleware(CsrfTokenMiddleware::class) // <-- add this
        ->routes($routes)
);
```

#### Configure routes

Create a route for acquiring CSRF-tokens from the frontend application.

```php
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Router\Route;

Route::options('/csrf-token')
    ->action(static function (
        ResponseFactoryInterface $responseFactory,
        CsrfTokenInterface $token
    ): ResponseInterface {
        $tokenValue = $token->getValue();

        $response = $responseFactory->createResponse()
            ->withHeader(Header::ALLOW, Method::OPTIONS)
            ->withHeader('X-CSRF-TOKEN', $tokenValue);

        $response->getBody()->write($tokenValue);

        return $response;
    }),
```

#### Configure frontend requests

On the frontend first make a request to the configured endpoint and acquire a CSRF-token to use it in the subsequent requests.

```js
let response = await fetch('https://api.example.com/csrf-token');

let csrfToken = await response.text();
// OR
let csrfToken = response.headers.get('X-CSRF-TOKEN');
```

Add to all requests a custom header defined in the `CsrfTokenMiddleware` with acquired CSRF-token value.

```js
let response = fetch('https://api.example.com/whoami', {
  headers: {
    "X-CSRF-TOKEN": csrfToken
  }
});
```

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
