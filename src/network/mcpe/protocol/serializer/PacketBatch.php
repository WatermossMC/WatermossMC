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

namespace watermossmc\network\mcpe\protocol\serializer;

use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\PacketPool;
use watermossmc\utils\BinaryDataException;
use watermossmc\utils\BinaryStream;

use function strlen;

class PacketBatch
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @phpstan-return \Generator<int, string, void, void>
	 * @throws PacketDecodeException
	 */
	final public static function decodeRaw(BinaryStream $stream) : \Generator
	{
		$c = 0;
		while (!$stream->feof()) {
			try {
				$length = $stream->getUnsignedVarInt();
				$buffer = $stream->get($length);
			} catch (BinaryDataException $e) {
				throw new PacketDecodeException("Error decoding packet $c in batch: " . $e->getMessage(), 0, $e);
			}
			yield $buffer;
			$c++;
		}
	}

	/**
	 * @param string[] $packets
	 * @phpstan-param list<string> $packets
	 */
	final public static function encodeRaw(BinaryStream $stream, array $packets) : void
	{
		foreach ($packets as $packet) {
			$stream->putUnsignedVarInt(strlen($packet));
			$stream->put($packet);
		}
	}

	/**
	 * @phpstan-return \Generator<int, Packet, void, void>
	 * @throws PacketDecodeException
	 */
	final public static function decodePackets(BinaryStream $stream, PacketPool $packetPool) : \Generator
	{
		$c = 0;
		foreach (self::decodeRaw($stream) as $packetBuffer) {
			$packet = $packetPool->getPacket($packetBuffer);
			if ($packet !== null) {
				try {
					$packet->decode(PacketSerializer::decoder($packetBuffer, 0));
				} catch (PacketDecodeException $e) {
					throw new PacketDecodeException("Error decoding packet $c in batch: " . $e->getMessage(), 0, $e);
				}
				yield $packet;
			} else {
				throw new PacketDecodeException("Unknown packet $c in batch");
			}
			$c++;
		}
	}

	/**
	 * @param Packet[] $packets
	 * @phpstan-param list<Packet> $packets
	 */
	final public static function encodePackets(BinaryStream $stream, array $packets) : void
	{
		foreach ($packets as $packet) {
			$serializer = PacketSerializer::encoder();
			$packet->encode($serializer);
			$stream->putUnsignedVarInt(strlen($serializer->getBuffer()));
			$stream->put($serializer->getBuffer());
		}
	}
}
