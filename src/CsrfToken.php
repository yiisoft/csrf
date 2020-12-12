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

    public function getValue(): string
    {
        return TokenMask::apply(
            $this->fetcher->getValue()
        );
    }
}
