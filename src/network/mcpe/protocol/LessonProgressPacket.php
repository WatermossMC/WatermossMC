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

/**
 * Handled only in Education mode. Used to fire telemetry reporting on the client.
 */
class LessonProgressPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LESSON_PROGRESS_PACKET;

	public const ACTION_START = 0;
	public const ACTION_FINISH = 1;
	public const ACTION_RESTART = 2;

	private int $action;
	private int $score;
	private string $activityId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $action, int $score, string $activityId) : self
	{
		$result = new self();
		$result->action = $action;
		$result->score = $score;
		$result->activityId = $activityId;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getScore() : int
	{
		return $this->score;
	}

	public function getActivityId() : string
	{
		return $this->activityId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getVarInt();
		$this->score = $in->getVarInt();
		$this->activityId = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->action);
		$out->putVarInt($this->score);
		$out->putString($this->activityId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLessonProgress($this);
	}
}
