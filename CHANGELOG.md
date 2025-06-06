# Yii CSRF Protection Library Change Log

## 2.2.3 under development

- no changes in this release.

## 2.2.2 June 03, 2025

- Bug #79: Explicitly marking parameters as nullable (@Tigrov)

## 2.2.1 March 05, 2025

- Chg #75: Change PHP constraint in `composer.json` to `~7.4.0 || ~8.0.0 || ~8.1.0 || ~8.2.0 || ~8.3.0 || ~8.4.0` (@vjik)
- Enh #76: Add support of `yiisoft/session` version `^3.0` (@vjik)

## 2.2.0 November 14, 2024

- New #68: Add the `CsrfHeaderMiddleware` middleware to use custom HTTP header to prevent forgery of requests (@olegbaturin)
- Chg #71: Deprecate `CsrfMiddleware` in favor of `CsrfTokenMiddleware` (@ev-gor)
- Enh #68: Add the `CsrfMiddleware::withSafeMethods()` method to configure a custom safe HTTP methods list (@olegbaturin)

## 2.1.1 May 08, 2024

- Enh #55: Add support for `psr/http-message` version `^2.0` (@vjik)

## 2.1.0 February 08, 2024

- New #53: Add `StubCsrfToken` (@hacan359)
- Bug #36: Explicitly add transitive dependencies `yiisoft/strings`, `psr/http-server-handler`
  and `ext-hash` (@vjik, @xepozz)

## 2.0.0 February 14, 2023

- Chg #43: Adapt configuration group names to Yii conventions (@vjik)
- Enh #44: Add support of `yiisoft/session` version `^2.0` (@vjik)

## 1.2.0 November 22, 2021

- Chg #31: Update `yiisoft/http` dependency (@devanych)
- Enh #30: Add a custom failure handler feature to `CsrfMiddleware` (@solventt, @devanych)

## 1.1.0 October 21, 2021

- New #29: Add methods `CsrfMiddleware::getParameterName()` and `CsrfMiddleware::getHeaderName()` (@vjik)

## 1.0.3 August 30, 2021

- Chg #28: Use definitions from `yiisoft/definitions` in configuration (@vjik)

## 1.0.2 April 13, 2021

- Chg: Adjust config for `yiisoft/factory` changes (@vjik, @samdark)

## 1.0.1 March 23, 2021

- Chg: Adjust config for new config plugin (@samdark)

## 1.0.0 February 23, 2021

- Initial release.
