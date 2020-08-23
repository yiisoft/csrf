<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Yiisoft\Csrf\CsrfToken;

class TestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        CsrfToken::initialize();
    }
}
