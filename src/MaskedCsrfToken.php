<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use Yiisoft\Security\TokenMask;

/**
 * Masked CSRF token applies masking to a token string. It makes BREACH attack impossible so it is safe to use it
 * in HTML to be later passed to the next request either as a hidden form field or via JavaScript async request.
 *
 * @see TokenMask
 */
final class MaskedCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenInterface $token;

    public function __construct(CsrfTokenInterface $token)
    {
        $this->token = $token;
    }

    public function getValue(): string
    {
        return TokenMask::apply(
            $this->token->getValue()
        );
    }

    public function validate(string $token): bool
    {
        return $this->token->validate(
            TokenMask::remove($token)
        );
    }
}
