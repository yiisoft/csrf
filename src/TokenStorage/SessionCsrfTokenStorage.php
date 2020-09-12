<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenStorage;

use Yiisoft\Session\SessionInterface;

class SessionCsrfTokenStorage implements CsrfTokenStorageInterface
{

    public const KEY = '_csrf';

    private string $key;

    private SessionInterface $session;

    public function __construct(SessionInterface $session, string $key = self::KEY)
    {
        $this->key = $key;
        $this->session = $session;
    }

    public function get(): ?string
    {
        return $this->session->get($this->key);
    }

    public function set(string $token): void
    {
        $this->session->set($this->key, $token);
    }

    public function remove(): void
    {
        $this->session->remove($this->key);
    }
}
