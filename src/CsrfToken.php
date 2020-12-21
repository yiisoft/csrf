<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Yiisoft\Csrf\TokenFetcher\CsrfTokenFetcherInterface;
use Yiisoft\Security\TokenMask;

final class CsrfToken
{
    private CsrfTokenFetcherInterface $fetcher;

    public function __construct(CsrfTokenFetcherInterface $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @return string The token string valid for the next request with the mask applied. It is safe to render it in the
     * current HTML page to be later passed to the next request either as a hidden form field or via JavaScript
     * async request.
     */
    public function getValue(): string
    {
        return TokenMask::apply(
            $this->fetcher->getValue()
        );
    }
}
