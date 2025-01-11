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

use function count;

class AnimateEntityPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ANIMATE_ENTITY_PACKET;

	private string $animation;
	private string $nextState;
	private string $stopExpression;
	private int $stopExpressionVersion;
	private string $controller;
	private float $blendOutTime;
	/**
	 * @var int[]
	 * @phpstan-var list<int>
	 */
	private array $actorRuntimeIds;

	/**
	 * @generate-create-func
	 * @param int[] $actorRuntimeIds
	 * @phpstan-param list<int> $actorRuntimeIds
	 */
	public static function create(
		string $animation,
		string $nextState,
		string $stopExpression,
		int $stopExpressionVersion,
		string $controller,
		float $blendOutTime,
		array $actorRuntimeIds,
	) : self {
		$result = new self();
		$result->animation = $animation;
		$result->nextState = $nextState;
		$result->stopExpression = $stopExpression;
		$result->stopExpressionVersion = $stopExpressionVersion;
		$result->controller = $controller;
		$result->blendOutTime = $blendOutTime;
		$result->actorRuntimeIds = $actorRuntimeIds;
		return $result;
	}

	public function getAnimation() : string
	{
		return $this->animation;
	}

	public function getNextState() : string
	{
		return $this->nextState;
	}

	public function getStopExpression() : string
	{
		return $this->stopExpression;
	}

	public function getStopExpressionVersion() : int
	{
		return $this->stopExpressionVersion;
	}

	public function getController() : string
	{
		return $this->controller;
	}

	public function getBlendOutTime() : float
	{
		return $this->blendOutTime;
	}

	/**
	 * @return int[]
	 * @phpstan-return list<int>
	 */
	public function getActorRuntimeIds() : array
	{
		return $this->actorRuntimeIds;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->animation = $in->getString();
		$this->nextState = $in->getString();
		$this->stopExpression = $in->getString();
		$this->stopExpressionVersion = $in->getLInt();
		$this->controller = $in->getString();
		$this->blendOutTime = $in->getLFloat();
		$this->actorRuntimeIds = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->actorRuntimeIds[] = $in->getActorRuntimeId();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->animation);
		$out->putString($this->nextState);
		$out->putString($this->stopExpression);
		$out->putLInt($this->stopExpressionVersion);
		$out->putString($this->controller);
		$out->putLFloat($this->blendOutTime);
		$out->putUnsignedVarInt(count($this->actorRuntimeIds));
		foreach ($this->actorRuntimeIds as $id) {
			$out->putActorRuntimeId($id);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAnimateEntity($this);
	}
}
