<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenFetcher;

interface CsrfTokenFetcherInterface
{
    public function getValue(): string;
}
