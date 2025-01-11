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

namespace watermossmc\stats;

use Ramsey\Uuid\Uuid;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\player\Player;
use watermossmc\scheduler\AsyncTask;
use watermossmc\Server;
use watermossmc\utils\Internet;
use watermossmc\utils\Process;
use watermossmc\utils\Utils;
use watermossmc\VersionInfo;
use watermossmc\YmlServerProperties;

use function array_map;
use function array_values;
use function count;
use function json_encode;
use function md5;
use function microtime;
use function php_uname;
use function strlen;

use const JSON_THROW_ON_ERROR;
use const PHP_VERSION;

class SendUsageTask extends AsyncTask
{
	public const TYPE_OPEN = 1;
	public const TYPE_STATUS = 2;
	public const TYPE_CLOSE = 3;

	public string $endpoint;
	public string $data;

	/**
	 * @param string[] $playerList
	 * @phpstan-param array<string, string> $playerList
	 */
	public function __construct(Server $server, int $type, array $playerList = [])
	{
		$endpoint = "http://" . $server->getConfigGroup()->getPropertyString(YmlServerProperties::ANONYMOUS_STATISTICS_HOST, "stats.watermossmc.net") . "/";

		$data = [];
		$data["uniqueServerId"] = $server->getServerUniqueId()->toString();
		$data["uniqueMachineId"] = Utils::getMachineUniqueId()->toString();
		$data["uniqueRequestId"] = Uuid::uuid3($server->getServerUniqueId()->toString(), microtime(false))->toString();

		switch ($type) {
			case self::TYPE_OPEN:
				$data["event"] = "open";

				$version = VersionInfo::VERSION();

				$data["server"] = [
					"port" => $server->getPort(),
					"software" => $server->getName(),
					"fullVersion" => $version->getFullVersion(true),
					"version" => $version->getFullVersion(false),
					"build" => $version->getBuild(),
					"api" => $server->getApiVersion(),
					"minecraftVersion" => $server->getVersion(),
					"protocol" => ProtocolInfo::CURRENT_PROTOCOL
				];

				$data["system"] = [
					"operatingSystem" => Utils::getOS(),
					"cores" => Utils::getCoreCount(),
					"phpVersion" => PHP_VERSION,
					"machine" => php_uname("a"),
					"release" => php_uname("r"),
					"platform" => php_uname("i")
				];

				$data["players"] = [
					"count" => 0,
					"limit" => $server->getMaxPlayers()
				];

				$plugins = [];

				foreach ($server->getPluginManager()->getPlugins() as $p) {
					$d = $p->getDescription();

					$plugins[$d->getName()] = [
						"name" => $d->getName(),
						"version" => $d->getVersion(),
						"enabled" => $p->isEnabled()
					];
				}

				$data["plugins"] = $plugins;

				break;
			case self::TYPE_STATUS:
				$data["event"] = "status";

				$data["server"] = [
					"ticksPerSecond" => $server->getTicksPerSecondAverage(),
					"tickUsage" => $server->getTickUsageAverage(),
					"ticks" => $server->getTick()
				];

				//This anonymizes the user ids so they cannot be reversed to the original
				$playerList = array_map('md5', $playerList);

				$players = array_map(function (Player $p) : string { return md5($p->getUniqueId()->getBytes()); }, $server->getOnlinePlayers());

				$data["players"] = [
					"count" => count($players),
					"limit" => $server->getMaxPlayers(),
					"currentList" => array_values($players),
					"historyList" => array_values($playerList)
				];

				$info = Process::getAdvancedMemoryUsage();
				$data["system"] = [
					"mainMemory" => $info[0],
					"totalMemory" => $info[1],
					"availableMemory" => $info[2],
					"threadCount" => Process::getThreadCount()
				];

				break;
			case self::TYPE_CLOSE:
				$data["event"] = "close";
				$data["crashing"] = $server->isRunning();
				break;
		}

		$this->endpoint = $endpoint . "api/post";
		$this->data = json_encode($data, /*JSON_PRETTY_PRINT |*/ JSON_THROW_ON_ERROR);
	}

	public function onRun() : void
	{
		Internet::postURL($this->endpoint, $this->data, 5, [
			"Content-Type: application/json",
			"Content-Length: " . strlen($this->data)
		]);
	}
}
