<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\TokenStorage;

/**
 * Token storage persists a token between requests.
 */
interface CsrfTokenStorageInterface
{
    /**
     * Read CSRF token from storage
     */
    public function get(): ?string;

    /**
     * Write CSRF token into storage
     *
     * @param string $token
     */
    public function set(string $token): void;

    /**
     * Remove CSRF token from storage
     */
    public function remove(): void;
}
