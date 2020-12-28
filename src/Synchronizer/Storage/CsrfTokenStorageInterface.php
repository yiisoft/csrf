<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer\Storage;

/**
 * Token storage persists a token between requests.
 */
interface CsrfTokenStorageInterface
{
    /**
     * Read CSRF token from a storage.
     */
    public function get(): ?string;

    /**
     * Write CSRF token into a storage.
     *
     * @param string $token
     */
    public function set(string $token): void;

    /**
     * Remove CSRF token from a storage.
     */
    public function remove(): void;
}
