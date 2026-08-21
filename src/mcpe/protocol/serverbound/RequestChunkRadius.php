<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol\serverbound;

use watermossmc\mcpe\protocol\Packet;

use watermossmc\binary\McpeBinary;

final class RequestChunkRadius extends Packet
{
	/**
	 * @return array{radius:int, maxRadius:int}
	 */
	public static function read(string $p, int &$o): array
	{
		$radius = McpeBinary::readSignedVarInt($p, $o);
		$maxRadius = McpeBinary::readByte($p, $o);

		return [
			'radius' => $radius,
			'maxRadius' => $maxRadius,
		];
	}
}
