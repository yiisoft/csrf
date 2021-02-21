<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Synchronizer;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Synchronizer\Generator\CsrfTokenGeneratorInterface;
use Yiisoft\Csrf\Synchronizer\Storage\CsrfTokenStorageInterface;

/**
 * Stateful CSRF token that is a unique random string. It is stored it in persistent storage available only for
 * the currently logged in user. The same token is added to forms. When the form is submitted, token that came
 * from the form is compared against the token stored.
 *
 * The algorithm is also known as "Synchronizer Token".
 *
 * Do not forget to decorate the token with {@see \Yiisoft\Csrf\MaskedCsrfToken} to prevent BREACH attack.
 *
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern
 */
final class SynchronizerCsrfToken implements CsrfTokenInterface
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
