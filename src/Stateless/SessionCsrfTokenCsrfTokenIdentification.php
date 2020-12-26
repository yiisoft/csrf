<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateless;

use Yiisoft\Session\SessionInterface;

final class SessionCsrfTokenCsrfTokenIdentification implements CsrfTokenIdentificationInterface
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getString(): string
    {
        $this->session->open();
        return (string)$this->session->getId();
    }
}
