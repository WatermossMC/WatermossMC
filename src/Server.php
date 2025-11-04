<?php

namespace WatermossMC;

use WatermossMC\Console;
use WatermossMC\Command\CommandManager;
use WatermossMC\Plugin\PluginLoader;
use WatermossMC\ResourcePack\ResourcePackManager;
use WatermossMC\BehaviorPack\BehaviorPackManager;
use WatermossMC\World\World;
use raklib\server\Server as RakLibServer;
use raklib\server\ServerSocket;
use raklib\utils\InternetAddress;
use raklib\server\ServerEventListener;
use raklib\server\ServerEventSource;
use raklib\utils\ExceptionTraceCleaner;
use Logger;

class Server
{
	private Console $console;
	private CommandManager $commandManager;
	private PluginLoader $pluginLoader;
	private ResourcePackManager $resourcePackManager;
	private BehaviorPackManager $behaviorPackManager;
	private World $world;
	private RakLibServer $raklibServer;
	private Logger $logger;

	public function __construct()
	{
		echo "Starting WatermossMC...\n";

		$this->loadServerProperties();

		$this->console = new Console($this);
		$this->commandManager = new CommandManager($this);
		$this->pluginLoader = new PluginLoader();
		$this->resourcePackManager = new ResourcePackManager(__DIR__ . '/../resources/resource_packs');
		$this->behaviorPackManager = new BehaviorPackManager(__DIR__ . '/../resources/behavior_packs');
		$this->world = new World(__DIR__ . '/../resources/world', 12345);

		// === RakLib Server Setup ===
		$this->logger = new \Logger("RakLib");
		$bindAddr = new InternetAddress("0.0.0.0", 19132, 4);
		$socket = new ServerSocket($bindAddr);

		$eventListener = new class implements ServerEventListener {
			public function onClientConnect(int $sessionId, string $ip, int $port, int $clientId): void {
				echo "[RakLib] Client connected: $ip:$port (Session: $sessionId)\n";
			}
			public function onClientDisconnect(int $sessionId, string $reason): void {
				echo "[RakLib] Client disconnected: $sessionId ($reason)\n";
			}
			public function onRawPacketReceive(string $ip, int $port, string $payload): void {}
			public function onBandwidthStatsUpdate(int $sent, int $recv): void {
				echo "[RakLib] Bandwidth - Sent: $sent bytes, Received: $recv bytes\n";
			}
		};

		$eventSource = new ServerEventSource();
		$traceCleaner = new ExceptionTraceCleaner();

		$this->raklibServer = new RakLibServer(
			serverId: time(),
			logger: $this->logger,
			socket: $socket,
			maxMtuSize: 1492,
			protocolAcceptor: new \raklib\server\DefaultProtocolAcceptor(),
			eventSource: $eventSource,
			eventListener: $eventListener,
			traceCleaner: $traceCleaner
		);

		echo "RakLib server started on 0.0.0.0:19132\n";

		$this->pluginLoader->loadPlugins();
		$this->mainLoop();
	}

	private function loadServerProperties(): void
	{
		$file = __DIR__ . '/../resources/server.properties';
		if (!file_exists($file)) {
			exit("server.properties not found.\n");
		}

		$config = parse_ini_file($file);

		if (!is_array($config)) {
			echo "Error: Failed to parse server.properties.\n";
			return;
		}

		$motd = $config['motd'] ?? 'A WatermossMC Server';
		$onlineMode = $config['online_mode'] ?? true;

		echo "MOTD: " . $motd . "\n";
		echo "Online Mode: " . ($onlineMode ? "Enabled" : "Disabled") . "\n";
	}

	private function mainLoop(): void
	{
		while (true) {
			// Jalankan tick RakLib
			$this->raklibServer->tickProcessor();

			// Tick dunia dan sistem lainnya
			$this->world->tick();

			// Tangani input dari console
			$this->console->handleInput();

			usleep(50000); // 50ms
		}
	}

	public function getCommandManager(): CommandManager
	{
		return $this->commandManager;
	}

	public function getWorld(): World
	{
		return $this->world;
	}

	public function getPluginLoader(): PluginLoader
	{
		return $this->pluginLoader;
	}

	public function getResourcePackManager(): ResourcePackManager
	{
		return $this->resourcePackManager;
	}

	public function getBehaviorPackManager(): BehaviorPackManager
	{
		return $this->behaviorPackManager;
	}
}
