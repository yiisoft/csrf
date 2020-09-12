<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Token;

use LogicException;

interface CsrfTokenInterface
{

    /**
     * @return string|null
     * @throws LogicException when CSRF token is not defined
     */
    public function getValue(): ?string;

    /**
     * @param string $token
     * @throws LogicException when the CSRF token is already set
     */
    public function setValue(string $token): void;
}
