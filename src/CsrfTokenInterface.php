<?php

declare(strict_types=1);

namespace Yiisoft\Csrf;

/**
 * Token reader returns currently valid token as string.
 *
 * Do not use directly. See {@see MaskedCsrfToken::getValue()}.
 */
interface CsrfTokenInterface
{
    /**
     * @return string The currently valid token as string.
     */
    public function getValue(): string;
}
