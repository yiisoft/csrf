<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Stateful;

use Yiisoft\Csrf\CsrfTokenInterface;

/**
 * Stateful CSRF token is a unique random string. It is stored it in persistent storage available only for
 * the currently logged in user. The same token is added to forms. When the form is submitted, token that came
 * from the form is compared against the token stored.
 *
 * The algorithm is also known as "Synchronizer Token".
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern
 */
final class StatefulCsrfToken implements CsrfTokenInterface
{
    private CsrfTokenGeneratorInterface $generator;
    private CsrfTokenStorageInterface $storage;

    public function __construct(
        CsrfTokenGeneratorInterface $generator,
        CsrfTokenStorageInterface $storage
    ) {
        $this->generator = $generator;
        $this->storage = $storage;
    }

    public function getValue(): string
    {
        $token = $this->storage->get();
        if (empty($token)) {
            $token = $this->generator->generate();
            $this->storage->set($token);
        }

        return $token;
    }

    public function validate(string $token): bool
    {
        return hash_equals($this->getValue(), $token);
    }
}
