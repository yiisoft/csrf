<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Yiisoft\Security\TokenMask;

/**
 * Masked CSRF token is a decorator that applies masking to a token so it is safe to use it in HTML to be later passed
 * to the next request either as a hidden form field or via JavaScript async request.
 *
 * @see TokenMask
 */
final class MaskedCsrfToken
{
    private CsrfTokenInterface $token;

    public function __construct(CsrfTokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @return string The currently valid token string with the mask applied.
     */
    public function getValue(): string
    {
        return TokenMask::apply(
            $this->token->getValue()
        );
    }
}
