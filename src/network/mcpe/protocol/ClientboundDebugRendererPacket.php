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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class ClientboundDebugRendererPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DEBUG_RENDERER_PACKET;

	public const TYPE_CLEAR = 1;
	public const TYPE_ADD_CUBE = 2;

	private int $type;

	//TODO: if more types are added, we'll probably want to make a separate data type and interfaces
	private string $text;
	private Vector3 $position;
	private float $red;
	private float $green;
	private float $blue;
	private float $alpha;
	private int $durationMillis;

	private static function base(int $type) : self
	{
		$result = new self();
		$result->type = $type;
		return $result;
	}

	public static function clear() : self
	{
		return self::base(self::TYPE_CLEAR);
	}

	public static function addCube(string $text, Vector3 $position, float $red, float $green, float $blue, float $alpha, int $durationMillis) : self
	{
		$result = self::base(self::TYPE_ADD_CUBE);
		$result->text = $text;
		$result->position = $position;
		$result->red = $red;
		$result->green = $green;
		$result->blue = $blue;
		$result->alpha = $alpha;
		$result->durationMillis = $durationMillis;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	public function getRed() : float
	{
		return $this->red;
	}

	public function getGreen() : float
	{
		return $this->green;
	}

	public function getBlue() : float
	{
		return $this->blue;
	}

	public function getAlpha() : float
	{
		return $this->alpha;
	}

	public function getDurationMillis() : int
	{
		return $this->durationMillis;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getLInt();

		switch ($this->type) {
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$this->text = $in->getString();
				$this->position = $in->getVector3();
				$this->red = $in->getLFloat();
				$this->green = $in->getLFloat();
				$this->blue = $in->getLFloat();
				$this->alpha = $in->getLFloat();
				$this->durationMillis = $in->getLLong();
				break;
			default:
				throw new PacketDecodeException("Unknown type " . $this->type);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLInt($this->type);

		switch ($this->type) {
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$out->putString($this->text);
				$out->putVector3($this->position);
				$out->putLFloat($this->red);
				$out->putLFloat($this->green);
				$out->putLFloat($this->blue);
				$out->putLFloat($this->alpha);
				$out->putLLong($this->durationMillis);
				break;
			default:
				throw new \InvalidArgumentException("Unknown type " . $this->type);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleClientboundDebugRenderer($this);
	}
}
