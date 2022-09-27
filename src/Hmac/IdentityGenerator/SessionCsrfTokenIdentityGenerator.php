<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac\IdentityGenerator;

use Yiisoft\Session\SessionInterface;

/**
 * Session based CSRF token identification.
 */
final class SessionCsrfTokenIdentityGenerator implements CsrfTokenIdentityGeneratorInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function generate(): string
    {
        return (string)$this->session->getId();
    }
}
