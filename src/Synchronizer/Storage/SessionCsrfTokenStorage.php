<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer\Storage;

use Yiisoft\Session\SessionInterface;

use function is_string;

/**
 * Persists a token between requests in a user session.
 */
class SessionCsrfTokenStorage implements CsrfTokenStorageInterface
{
    public const KEY = '_csrf';

    /**
     * @var string Session key used to store data.
     */
    private string $key;

    private SessionInterface $session;

    /**
     * @param string $key Session key used to store data. Default is "_csrf".
     */
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
