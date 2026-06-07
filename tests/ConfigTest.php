<?php

declare(strict_types=1);

namespace Yiisoft\Csrf\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Hmac\HmacCsrfToken;
use Yiisoft\Csrf\MaskedCsrfToken;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Session\NullSession;
use Yiisoft\Session\SessionInterface;

use function dirname;
use function getenv;
use function putenv;

final class ConfigTest extends TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $csrfToken = $container->get(CsrfTokenInterface::class);
        $synchronizerCsrfToken = $container->get(SynchronizerCsrfToken::class);
        $hmacCsrfToken = $container->get(HmacCsrfToken::class);

        $this->assertInstanceOf(MaskedCsrfToken::class, $csrfToken);
        $this->assertInstanceOf(HmacCsrfToken::class, $this->getDecoratedToken($csrfToken));
        $this->assertInstanceOf(SynchronizerCsrfToken::class, $synchronizerCsrfToken);
        $this->assertInstanceOf(HmacCsrfToken::class, $hmacCsrfToken);
    }

    public function testHmacSecretKeyCanBeSetViaEnvironment(): void
    {
        $oldSecretKey = getenv('YII_CSRF_SECRET_KEY');

        try {
            putenv('YII_CSRF_SECRET_KEY=test-secret-key');

            $params = $this->getParams();

            $this->assertSame('test-secret-key', $params['yiisoft/csrf']['hmacToken']['secretKey']);
            $this->assertSame(300, $params['yiisoft/csrf']['hmacToken']['lifetime']);
        } finally {
            if ($oldSecretKey === false) {
                putenv('YII_CSRF_SECRET_KEY');
            } else {
                putenv('YII_CSRF_SECRET_KEY=' . $oldSecretKey);
            }
        }
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

    private function getDecoratedToken(MaskedCsrfToken $csrfToken): CsrfTokenInterface
    {
        $property = new ReflectionProperty(MaskedCsrfToken::class, 'token');
        $property->setAccessible(true);

        return $property->getValue($csrfToken);
    }
}
