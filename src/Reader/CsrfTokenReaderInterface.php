<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Reader;

/**
 * Token reader returns currently valid token as string.
 *
 * Do not use directly. See {@see CsrfToken::getValue()}.
 */
interface CsrfTokenReaderInterface
{
    /**
     * @return string The currently valid token as string.
     */
    public function getValue(): string;
}
