<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', false)
    ->addPathToScan(__DIR__ . '/src', false)
    ->addPathToScan(__DIR__ . '/tests', true)
    // Used in "config/di-web.php", but not listed in "composer.json" because it's a shadow dependency of "yiisoft/di".
    ->ignoreErrorsOnPackageAndPath('yiisoft/definitions', __DIR__ . '/config/di-web.php', [ErrorType::SHADOW_DEPENDENCY])
    // Virtual packages: they only mark that a PSR interface implementation must be provided, so no direct code usage is expected.
    ->ignoreErrorsOnPackage('psr/http-factory-implementation', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('psr/http-message-implementation', [ErrorType::UNUSED_DEPENDENCY]);
