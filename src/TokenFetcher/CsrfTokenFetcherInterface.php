<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenFetcher;

/**
 * Token fetcher returns currently valid token as string.
 *
 * @internal Do not use directly. See {@see CsrfToken::getValue()}.
 */
interface CsrfTokenFetcherInterface
{
    /**
     * @return string The currently valid token as string.
     */
    public function getValue(): string;
}
