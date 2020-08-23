<?php

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
