<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer\Storage;

use Yiisoft\Session\SessionInterface;

use function is_string;

/**
 * Persists a token between requests in a user session.
 *
 * @psalm-suppress ClassMustBeFinal Class will be marked as final in the next major version.
 * See https://github.com/yiisoft/csrf/issues/74
 */
class SessionCsrfTokenStorage implements CsrfTokenStorageInterface
{
    /**
     * @psalm-suppress MissingClassConstType
     */
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
