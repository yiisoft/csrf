<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

use LogicException;
use Yiisoft\Csrf\Token\CsrfTokenInterface;
use Yiisoft\Csrf\Token\StateCsrfTokenInterface;
use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;
use Yiisoft\Security\TokenMask;

final class CsrfTokenService
{
    private CsrfTokenInterface $token;
    private ?CsrfTokenStorageInterface $storage;

    public function __construct(
        CsrfTokenInterface $token,
        ?CsrfTokenStorageInterface $storage = null
    ) {
        $this->token = $token;
        $this->storage = $storage;
    }

    /**
     * @throws LogicException when CSRF token storage is not defined for tokens with state
     *
     * @return string
     */
    public function getValue(): string
    {
        return TokenMask::apply(
            $this->getTokenValue()
        );
    }

    public function validate(string $token): bool
    {
        return hash_equals($this->getTokenValue(), TokenMask::remove($token));
    }

    private function getTokenValue(): string
    {
        return $this->token instanceof StateCsrfTokenInterface
            ? $this->getStateTokenValue()
            : $this->getStatelessTokenValue();
    }

    private function getStatelessTokenValue(): string
    {
        return $this->token->generate();
    }

    /**
     * @throws LogicException when CSRF token storage is not defined
     *
     * @return string
     */
    private function getStateTokenValue(): string
    {
        if ($this->storage === null) {
            throw new LogicException('CSRF token storage is not defined.');
        }

        $token = $this->storage->get();
        if (empty($token)) {
            $token = $this->token->generate();
            $this->storage->set($token);
        }

        return $token;
    }
}
