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
    ->ignoreErrorsOnPackageAndPath('yiisoft/definitions', __DIR__ . '/config/di-web.php', [ErrorType::SHADOW_DEPENDENCY]);
