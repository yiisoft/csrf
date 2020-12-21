<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Yiisoft\Csrf\Reader\CsrfTokenReaderInterface;
use Yiisoft\Security\TokenMask;

final class CsrfToken
{
    private CsrfTokenReaderInterface $reader;

    public function __construct(CsrfTokenReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return string The token string valid for the next request with the mask applied. It is safe to render it in the
     * current HTML page to be later passed to the next request either as a hidden form field or via JavaScript
     * async request.
     */
    public function getValue(): string
    {
        return TokenMask::apply(
            $this->reader->getValue()
        );
    }
}
