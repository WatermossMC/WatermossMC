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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\CompressionAlgorithm;

/**
 * This is the first packet sent by the server in a game session, in response to a network settings request (only if
 * protocol versions are a match). It includes values for things like which compression algorithm to use, size threshold
 * for compressing packets, and more.
 */
class NetworkSettingsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_SETTINGS_PACKET;

	public const COMPRESS_NOTHING = 0;
	public const COMPRESS_EVERYTHING = 1;

	private int $compressionThreshold;
	private int $compressionAlgorithm;
	private bool $enableClientThrottling;
	private int $clientThrottleThreshold;
	private float $clientThrottleScalar;

	/**
	 * @generate-create-func
	 */
	public static function create(int $compressionThreshold, int $compressionAlgorithm, bool $enableClientThrottling, int $clientThrottleThreshold, float $clientThrottleScalar) : self
	{
		$result = new self();
		$result->compressionThreshold = $compressionThreshold;
		$result->compressionAlgorithm = $compressionAlgorithm;
		$result->enableClientThrottling = $enableClientThrottling;
		$result->clientThrottleThreshold = $clientThrottleThreshold;
		$result->clientThrottleScalar = $clientThrottleScalar;
		return $result;
	}

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	public function getCompressionThreshold() : int
	{
		return $this->compressionThreshold;
	}

	/**
	 * @see CompressionAlgorithm
	 */
	public function getCompressionAlgorithm() : int
	{
		return $this->compressionAlgorithm;
	}

	public function isEnableClientThrottling() : bool
	{
		return $this->enableClientThrottling;
	}

	public function getClientThrottleThreshold() : int
	{
		return $this->clientThrottleThreshold;
	}

	public function getClientThrottleScalar() : float
	{
		return $this->clientThrottleScalar;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->compressionThreshold = $in->getLShort();
		$this->compressionAlgorithm = $in->getLShort();
		$this->enableClientThrottling = $in->getBool();
		$this->clientThrottleThreshold = $in->getByte();
		$this->clientThrottleScalar = $in->getLFloat();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLShort($this->compressionThreshold);
		$out->putLShort($this->compressionAlgorithm);
		$out->putBool($this->enableClientThrottling);
		$out->putByte($this->clientThrottleThreshold);
		$out->putLFloat($this->clientThrottleScalar);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleNetworkSettings($this);
	}
}
