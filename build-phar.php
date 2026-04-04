<?php

declare(strict_types=1);

$pharFile = __DIR__ . '/watermossmc.phar';

if (file_exists($pharFile)) {
    unlink($pharFile);
}

if (!extension_loaded('phar')) {
    fwrite(STDERR, "The phar extension is required to build the archive.\n");
    exit(1);
}

try {
    $phar = new Phar($pharFile);
    $phar->startBuffering();
    $phar->buildFromDirectory(__DIR__, '/\.(php|properties)$/i');
    $phar->setStub(<<<'STUB'
<?php
Phar::mapPhar('watermossmc.phar');
require 'phar://watermossmc.phar/server.php';
__HALT_COMPILER();
STUB
);
    $phar->stopBuffering();
    echo "Created watermossmc.phar\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Failed to build PHAR: " . $e->getMessage() . "\n");
    exit(1);
}
