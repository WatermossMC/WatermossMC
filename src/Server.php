<?php

namespace WatermossMC;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use raklib\server\Server as RakLibServer;
use raklib\server\ServerSocket;
use raklib\server\ServerEventListener;
use raklib\server\DummyLogger;
use raklib\server\DummyServerEventSource;
use raklib\server\ProtocolAcceptor;
use raklib\utils\InternetAddress;
use raklib\protocol\UnconnectedPing;
use raklib\protocol\UnconnectedPong;
use raklib\protocol\PacketSerializer;

class Server
{
	private ?Console $console = null;
	private ?RakLibServer $raklibServer = null;
	private array $config = [];
	private string $motd = "WatermossMC Server";
	private string $version = "1.21.120";
	private int $protocol = ProtocolInfo::PROTOCOL_VERSION;
	private int $maxPlayers = 20;
	private int $onlinePlayers = 0;

	public function __construct()
	{
		echo "Starting WatermossMC...\n";

		$this->loadServerProperties();

		$this->console = new Console($this);

		$logger = new DummyLogger();
		$socket = new ServerSocket(new InternetAddress("0.0.0.0", 19132, 4));

		$eventListener = new class($this) implements ServerEventListener {
			private Server $server;

			public function __construct(Server $server)
			{
				$this->server = $server;
			}

			public function onClientConnect(int $sessionId, string $ip, int $port, int $clientId): void
			{
				echo "[RakLib] Client connected: $ip:$port\n";
				$this->server->increasePlayerCount();
			}

			public function onClientDisconnect(int $sessionId, int $reason): void
			{
				echo "[RakLib] Client disconnected: $sessionId (reason=$reason)\n";
				$this->server->decreasePlayerCount();
			}

			public function onRawPacketReceive(string $ip, int $port, string $payload): void
			{
				if (strlen($payload) > 0 && ord($payload[0]) === UnconnectedPing::ID) {
					$pk = new UnconnectedPing();
					$pk->decode(new PacketSerializer($payload));

					$pong = new UnconnectedPong();
					$pong->serverID = $this->server->getServerId();
					$pong->pingID = $pk->pingID;
					$pong->serverName = $this->server->getMotdString();

					$out = new PacketSerializer();
					$pong->encode($out);

					$this->server->getRakLibServer()->sendRaw($ip, $port, $out->getBuffer());
					echo "[RakLib] Responded to ping from $ip:$port\n";
				}
			}

			public function onPacketAck(int $sessionId, int $identifierACK): void {}
			public function onPacketReceive(int $sessionId, string $payload): void {}
			public function onPingMeasure(int $sessionId, int $pingMS): void {}
			public function onBandwidthStatsUpdate(int $sent, int $recv): void {}
		};

		$eventSource = new DummyServerEventSource();
		$protocolAcceptor = new ProtocolAcceptor();

		$this->raklibServer = new RakLibServer(
			time(),
			$logger,
			$socket,
			1492,
			$protocolAcceptor,
			$eventSource,
			$eventListener
		);

		echo "RakLib server started on 0.0.0.0:19132\n";
		echo "MOTD: {$this->motd}\n";
		echo "Version: {$this->version}\n";
		echo "Max Players: {$this->maxPlayers}\n";

		$this->mainLoop();
	}

	private function loadServerProperties(): void
	{
		$file = __DIR__ . '/../resources/server.properties';
		if (!file_exists($file)) {
			echo "Warning: server.properties not found, using defaults.\n";
			return;
		}

		$this->config = parse_ini_file($file);

		$this->motd = $this->config['motd'] ?? $this->motd;
		$this->version = $this->config['version'] ?? $this->version;
		$this->protocol = (int)($this->config['protocol'] ?? $this->protocol);
		$this->maxPlayers = (int)($this->config['max_players'] ?? $this->maxPlayers);
	}

	private function mainLoop(): void
	{
		while (true) {
			$this->raklibServer->tickProcessor();
			$this->console->handleInput();
			usleep(50000);
		}
	}

	public function getRakLibServer(): RakLibServer
	{
		return $this->raklibServer;
	}

	public function getMotdString(): string
	{
		return "MCPE;{$this->motd};{$this->protocol};{$this->version};{$this->onlinePlayers};{$this->maxPlayers};{$this->getServerId()};WatermossMC;Survival;1;19132;19132";
	}

	public function getServerId(): int
	{
		return 987654321;
	}

	public function increasePlayerCount(): void
	{
		$this->onlinePlayers++;
	}

	public function decreasePlayerCount(): void
	{
		if ($this->onlinePlayers > 0) {
			$this->onlinePlayers--;
		}
	}
}
