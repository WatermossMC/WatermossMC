<?php
$pharFile = 'WatermossMC.phar';
if (file_exists($pharFile)) {
    unlink($pharFile);
}
$phar = new Phar($pharFile);
$sourceDir = __DIR__ . '/src';
$phar->buildFromDirectory($sourceDir, '/\.php$/');
$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub('Server.php'));

echo "$pharFile has been Created.\n";
