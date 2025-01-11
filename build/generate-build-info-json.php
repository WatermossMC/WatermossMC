<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\VersionInfo;

require dirname(__DIR__) . '/vendor/autoload.php';

if (count($argv) !== 7) {
	fwrite(STDERR, "required args: <git hash> <tag name> <github repo (owner/name)> <build number> <github actions run ID> <PHP binary download URL>\n");
	exit(1);
}

echo json_encode([
	"php_version" => sprintf("%d.%d", PHP_MAJOR_VERSION, PHP_MINOR_VERSION), //deprecated
	"base_version" => VersionInfo::BASE_VERSION,
	"build" => (int) $argv[4],
	"is_dev" => VersionInfo::IS_DEVELOPMENT_BUILD,
	"channel" => VersionInfo::BUILD_CHANNEL,
	"git_commit" => $argv[1],
	"mcpe_version" => ProtocolInfo::MINECRAFT_VERSION_NETWORK,
	"date" => time(), //TODO: maybe we should embed this in VersionInfo?
	"details_url" => "https://github.com/$argv[3]/releases/tag/$argv[2]",
	"download_url" => "https://github.com/$argv[3]/releases/download/$argv[2]/WatermossMC.phar",
	"source_url" => "https://github.com/$argv[3]/tree/$argv[2]",
	"build_log_url" => "https://github.com/$argv[3]/actions/runs/$argv[5]",
	"php_download_url" => $argv[6],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
