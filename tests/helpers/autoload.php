<?php

namespace Recharg\Test;

require __DIR__ . '/../../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4(__NAMESPACE__ . '\\', __DIR__, TRUE);
$classLoader->register();
