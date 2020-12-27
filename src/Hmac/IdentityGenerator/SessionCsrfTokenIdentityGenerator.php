<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Hmac\IdentityGenerator;

use Yiisoft\Session\SessionInterface;

/**
 * Session based CSRF token identification. Returns the same token if the session ID is the same.
 */
final class SessionCsrfTokenIdentityGenerator implements CsrfTokenIdentityGeneratorInterface
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function generate(): string
    {
        return (string)$this->session->getId();
    }
}
