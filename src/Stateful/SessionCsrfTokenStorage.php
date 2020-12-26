<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateful;

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
        /** @var mixed $value */
        $value = $this->session->get($this->key);
        return is_string($value) ? $value : null;
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
