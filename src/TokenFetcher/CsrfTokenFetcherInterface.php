<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenFetcher;

/**
 * Token fetcher returns the token string valid for the next request.
 * @internal Do not use directly. See {@see CsrfToken::getValue()}.
 */
interface CsrfTokenFetcherInterface
{
    /**
     * @return string The token string valid for the next request.
     */
    public function getValue(): string;
}
