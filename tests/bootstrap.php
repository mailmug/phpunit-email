<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(
    dirname(__DIR__),
    '.env'
);

$dotenv->safeLoad();