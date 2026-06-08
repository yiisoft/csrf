<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Session\NullSession;
use Yiisoft\Session\SessionInterface;

use function dirname;

final class ConfigTest extends TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $csrfToken = $container->get(CsrfTokenInterface::class);
        $synchronizerCsrfToken = $container->get(SynchronizerCsrfToken::class);
        $hmacCsrfToken = $container->get(HmacCsrfToken::class);

        $this->assertInstanceOf(MaskedCsrfToken::class, $csrfToken);
        $this->assertInstanceOf(SynchronizerCsrfToken::class, $synchronizerCsrfToken);
        $this->assertInstanceOf(HmacCsrfToken::class, $hmacCsrfToken);
    }

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($params)
                + [SessionInterface::class => NullSession::class],
            ),
        );
    }

    private function getDiConfig(?array $params = null): array
    {
        if ($params === null) {
            $params = $this->getParams();
        }
        return require dirname(__DIR__) . '/config/di-web.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
