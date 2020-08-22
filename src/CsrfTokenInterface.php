<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

interface CsrfTokenInterface
{

    public function getValue(): ?string;

    public function setValue(string $token): void;
}
