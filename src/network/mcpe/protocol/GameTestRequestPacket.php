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
use watermossmc\network\mcpe\protocol\types\BlockPosition;

class GameTestRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::GAME_TEST_REQUEST_PACKET;

	public const ROTATION_0 = 0;
	public const ROTATION_90 = 1;
	public const ROTATION_180 = 2;
	public const ROTATION_270 = 3;

	private int $maxTestsPerBatch;
	private int $repeatCount;
	private int $rotation;
	private bool $stopOnFailure;
	private BlockPosition $testPosition;
	private int $testsPerRow;
	private string $testName;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $maxTestsPerBatch,
		int $repeatCount,
		int $rotation,
		bool $stopOnFailure,
		BlockPosition $testPosition,
		int $testsPerRow,
		string $testName,
	) : self {
		$result = new self();
		$result->maxTestsPerBatch = $maxTestsPerBatch;
		$result->repeatCount = $repeatCount;
		$result->rotation = $rotation;
		$result->stopOnFailure = $stopOnFailure;
		$result->testPosition = $testPosition;
		$result->testsPerRow = $testsPerRow;
		$result->testName = $testName;
		return $result;
	}

	public function getMaxTestsPerBatch() : int
	{
		return $this->maxTestsPerBatch;
	}

	public function getRepeatCount() : int
	{
		return $this->repeatCount;
	}

	/**
	 * @see self::ROTATION_*
	 */
	public function getRotation() : int
	{
		return $this->rotation;
	}

	public function isStopOnFailure() : bool
	{
		return $this->stopOnFailure;
	}

	public function getTestPosition() : BlockPosition
	{
		return $this->testPosition;
	}

	public function getTestsPerRow() : int
	{
		return $this->testsPerRow;
	}

	public function getTestName() : string
	{
		return $this->testName;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->maxTestsPerBatch = $in->getVarInt();
		$this->repeatCount = $in->getVarInt();
		$this->rotation = $in->getByte();
		$this->stopOnFailure = $in->getBool();
		$this->testPosition = $in->getSignedBlockPosition();
		$this->testsPerRow = $in->getVarInt();
		$this->testName = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->maxTestsPerBatch);
		$out->putVarInt($this->repeatCount);
		$out->putByte($this->rotation);
		$out->putBool($this->stopOnFailure);
		$out->putSignedBlockPosition($this->testPosition);
		$out->putVarInt($this->testsPerRow);
		$out->putString($this->testName);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleGameTestRequest($this);
	}
}
