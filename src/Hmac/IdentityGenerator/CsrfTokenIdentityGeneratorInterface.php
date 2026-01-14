<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac\IdentityGenerator;

use Yiisoft\Csrf\Hmac\HmacCsrfToken;

/**
 * Identity generator returns an ID to be used for the {@see HmacCsrfToken}.
 * For example, using session ID makes the session a token scope.
 */
interface CsrfTokenIdentityGeneratorInterface
{
    /**
     * @return string Identity to use for the token.
     */
    public function generate(): string;
}
