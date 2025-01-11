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

namespace watermossmc\network\mcpe\raklib;

use pmmp\thread\ThreadSafeArray;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\network\AdvancedNetworkInterface;
use watermossmc\network\mcpe\compression\ZlibCompressor;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\EntityEventBroadcaster;
use watermossmc\network\mcpe\NetworkSession;
use watermossmc\network\mcpe\PacketBroadcaster;
use watermossmc\network\mcpe\protocol\PacketPool;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Network;
use watermossmc\network\NetworkInterfaceStartException;
use watermossmc\network\PacketHandlingException;
use watermossmc\network\raklib\generic\DisconnectReason;
use watermossmc\network\raklib\generic\SocketException;
use watermossmc\network\raklib\protocol\EncapsulatedPacket;
use watermossmc\network\raklib\protocol\PacketReliability;
use watermossmc\network\raklib\server\ipc\RakLibToUserThreadMessageReceiver;
use watermossmc\network\raklib\server\ipc\UserToRakLibThreadMessageSender;
use watermossmc\network\raklib\server\ServerEventListener;
use watermossmc\network\raklib\utils\InternetAddress;
use watermossmc\player\GameMode;
use watermossmc\Server;
use watermossmc\thread\ThreadCrashException;
use watermossmc\timings\Timings;
use watermossmc\utils\Utils;
use watermossmc\YmlServerProperties;

use function addcslashes;
use function base64_encode;
use function implode;
use function mt_rand;
use function rtrim;
use function substr;

use const PHP_INT_MAX;

class RakLibInterface implements ServerEventListener, AdvancedNetworkInterface
{
	/**
	 * Sometimes this gets changed when the MCPE-layer protocol gets broken to the point where old and new can't
	 * communicate. It's important that we check this to avoid catastrophes.
	 */
	private const MCPE_RAKNET_PROTOCOL_VERSION = 11;

	private const MCPE_RAKNET_PACKET_ID = "\xfe";

	private Server $server;
	private Network $network;

	private int $rakServerId;
	private RakLibServer $rakLib;

	/** @var NetworkSession[] */
	private array $sessions = [];

	private RakLibToUserThreadMessageReceiver $eventReceiver;
	private UserToRakLibThreadMessageSender $interface;

	private int $sleeperNotifierId;

	private PacketBroadcaster $packetBroadcaster;
	private EntityEventBroadcaster $entityEventBroadcaster;
	private TypeConverter $typeConverter;

	public function __construct(
		Server $server,
		string $ip,
		int $port,
		bool $ipV6,
		PacketBroadcaster $packetBroadcaster,
		EntityEventBroadcaster $entityEventBroadcaster,
		TypeConverter $typeConverter
	) {
		$this->server = $server;
		$this->packetBroadcaster = $packetBroadcaster;
		$this->entityEventBroadcaster = $entityEventBroadcaster;
		$this->typeConverter = $typeConverter;

		$this->rakServerId = mt_rand(0, PHP_INT_MAX);

		$sleeperEntry = $this->server->getTickSleeper()->addNotifier(function () : void {
			Timings::$connection->startTiming();
			try {
				while ($this->eventReceiver->handle($this));
			} finally {
				Timings::$connection->stopTiming();
			}
		});
		$this->sleeperNotifierId = $sleeperEntry->getNotifierId();

		/** @phpstan-var ThreadSafeArray<int, string> $mainToThreadBuffer */
		$mainToThreadBuffer = new ThreadSafeArray();
		/** @phpstan-var ThreadSafeArray<int, string> $threadToMainBuffer */
		$threadToMainBuffer = new ThreadSafeArray();

		$this->rakLib = new RakLibServer(
			$this->server->getLogger(),
			$mainToThreadBuffer,
			$threadToMainBuffer,
			new InternetAddress($ip, $port, $ipV6 ? 6 : 4),
			$this->rakServerId,
			$this->server->getConfigGroup()->getPropertyInt(YmlServerProperties::NETWORK_MAX_MTU_SIZE, 1492),
			self::MCPE_RAKNET_PROTOCOL_VERSION,
			$sleeperEntry
		);
		$this->eventReceiver = new RakLibToUserThreadMessageReceiver(
			new PthreadsChannelReader($threadToMainBuffer)
		);
		$this->interface = new UserToRakLibThreadMessageSender(
			new PthreadsChannelWriter($mainToThreadBuffer)
		);
	}

	public function start() : void
	{
		$this->server->getLogger()->debug("Waiting for RakLib to start...");
		try {
			$this->rakLib->startAndWait();
		} catch (SocketException $e) {
			throw new NetworkInterfaceStartException($e->getMessage(), 0, $e);
		}
		$this->server->getLogger()->debug("RakLib booted successfully");
	}

	public function setNetwork(Network $network) : void
	{
		$this->network = $network;
	}

	public function tick() : void
	{
		if (!$this->rakLib->isRunning()) {
			$e = $this->rakLib->getCrashInfo();
			if ($e !== null) {
				throw new ThreadCrashException("RakLib crashed", $e);
			}
			throw new \Exception("RakLib Thread crashed without crash information");
		}
	}

	public function onClientDisconnect(int $sessionId, int $reason) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$session = $this->sessions[$sessionId];
			unset($this->sessions[$sessionId]);
			$session->onClientDisconnect(match($reason) {
				DisconnectReason::CLIENT_DISCONNECT => KnownTranslationFactory::watermossmc_disconnect_clientDisconnect(),
				DisconnectReason::PEER_TIMEOUT => KnownTranslationFactory::watermossmc_disconnect_error_timeout(),
				DisconnectReason::CLIENT_RECONNECT => KnownTranslationFactory::watermossmc_disconnect_clientReconnect(),
				default => "Unknown RakLib disconnect reason (ID $reason)"
			});
		}
	}

	public function close(int $sessionId) : void
	{
		if (isset($this->sessions[$sessionId])) {
			unset($this->sessions[$sessionId]);
			$this->interface->closeSession($sessionId);
		}
	}

	public function shutdown() : void
	{
		$this->server->getTickSleeper()->removeNotifier($this->sleeperNotifierId);
		$this->rakLib->quit();
	}

	public function onClientConnect(int $sessionId, string $address, int $port, int $clientID) : void
	{
		$session = new NetworkSession(
			$this->server,
			$this->network->getSessionManager(),
			PacketPool::getInstance(),
			new RakLibPacketSender($sessionId, $this),
			$this->packetBroadcaster,
			$this->entityEventBroadcaster,
			ZlibCompressor::getInstance(), //TODO: this shouldn't be hardcoded, but we might need the RakNet protocol version to select it
			$this->typeConverter,
			$address,
			$port
		);
		$this->sessions[$sessionId] = $session;
	}

	public function onPacketReceive(int $sessionId, string $packet) : void
	{
		if (isset($this->sessions[$sessionId])) {
			if ($packet === "" || $packet[0] !== self::MCPE_RAKNET_PACKET_ID) {
				$this->sessions[$sessionId]->getLogger()->debug("Non-FE packet received: " . base64_encode($packet));
				return;
			}
			//get this now for blocking in case the player was closed before the exception was raised
			$session = $this->sessions[$sessionId];
			$address = $session->getIp();
			$buf = substr($packet, 1);
			$name = $session->getDisplayName();
			try {
				$session->handleEncoded($buf);
			} catch (PacketHandlingException $e) {
				$logger = $session->getLogger();

				$session->disconnectWithError(
					reason: "Bad packet: " . $e->getMessage(),
					disconnectScreenMessage: KnownTranslationFactory::watermossmc_disconnect_error_badPacket()
				);
				//intentionally doesn't use logException, we don't want spammy packet error traces to appear in release mode
				$logger->debug(implode("\n", Utils::printableExceptionInfo($e)));

				$this->interface->blockAddress($address, 5);
			} catch (\Throwable $e) {
				//record the name of the player who caused the crash, to make it easier to find the reproducing steps
				$this->server->getLogger()->emergency("Crash occurred while handling a packet from session: $name");
				throw $e;
			}
		}
	}

	public function blockAddress(string $address, int $timeout = 300) : void
	{
		$this->interface->blockAddress($address, $timeout);
	}

	public function unblockAddress(string $address) : void
	{
		$this->interface->unblockAddress($address);
	}

	public function onRawPacketReceive(string $address, int $port, string $payload) : void
	{
		$this->network->processRawPacket($this, $address, $port, $payload);
	}

	public function sendRawPacket(string $address, int $port, string $payload) : void
	{
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function addRawPacketFilter(string $regex) : void
	{
		$this->interface->addRawPacketFilter($regex);
	}

	public function onPacketAck(int $sessionId, int $identifierACK) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$this->sessions[$sessionId]->handleAckReceipt($identifierACK);
		}
	}

	public function setName(string $name) : void
	{
		$info = $this->server->getQueryInformation();

		$this->interface->setName(
			implode(
				";",
				[
					"MCPE",
					rtrim(addcslashes($name, ";"), '\\'),
					ProtocolInfo::CURRENT_PROTOCOL,
					ProtocolInfo::MINECRAFT_VERSION_NETWORK,
					$info->getPlayerCount(),
					$info->getMaxPlayerCount(),
					$this->rakServerId,
					$this->server->getName(),
					match($this->server->getGamemode()) {
						GameMode::SURVIVAL => "Survival",
						GameMode::ADVENTURE => "Adventure",
						default => "Creative"
					}
				]
			) . ";"
		);
	}

	public function setPortCheck(bool $name) : void
	{
		$this->interface->setPortCheck($name);
	}

	public function setPacketLimit(int $limit) : void
	{
		$this->interface->setPacketsPerTickLimit($limit);
	}

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void
	{
		$this->network->getBandwidthTracker()->add($bytesSentDiff, $bytesReceivedDiff);
	}

	public function putPacket(int $sessionId, string $payload, bool $immediate = true, ?int $receiptId = null) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$pk = new EncapsulatedPacket();
			$pk->buffer = self::MCPE_RAKNET_PACKET_ID . $payload;
			$pk->reliability = PacketReliability::RELIABLE_ORDERED;
			$pk->orderChannel = 0;
			$pk->identifierACK = $receiptId;

			$this->interface->sendEncapsulated($sessionId, $pk, $immediate);
		}
	}

	public function onPingMeasure(int $sessionId, int $pingMS) : void
	{
		if (isset($this->sessions[$sessionId])) {
			$this->sessions[$sessionId]->updatePing($pingMS);
		}
	}
}
