<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

$pharFile = __DIR__ . '/watermossmc.phar';

if (file_exists($pharFile)) {
    unlink($pharFile);
}

if (!\extension_loaded('phar')) {
    fwrite(\STDERR, "The phar extension is required to build the archive.\n");
    exit(1);
}

try {
    $phar = new Phar($pharFile);
    $phar->startBuffering();
    $phar->buildFromDirectory(__DIR__, '/\.(php|properties)$/i');
    $phar->setStub(
        <<<'STUB'
            <?php
            Phar::mapPhar('watermossmc.phar');
            require 'phar://watermossmc.phar/server.php';
            __HALT_COMPILER();
            STUB
    );
    $phar->stopBuffering();
    echo "Created watermossmc.phar\n";
} catch (Throwable $e) {
    fwrite(\STDERR, "Failed to build PHAR: " . $e->getMessage() . "\n");
    exit(1);
}
