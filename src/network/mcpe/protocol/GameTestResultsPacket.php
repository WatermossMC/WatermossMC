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

class GameTestResultsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::GAME_TEST_RESULTS_PACKET;

	private bool $success;
	private string $error;
	private string $testName;

	/**
	 * @generate-create-func
	 */
	public static function create(bool $success, string $error, string $testName) : self
	{
		$result = new self();
		$result->success = $success;
		$result->error = $error;
		$result->testName = $testName;
		return $result;
	}

	public function isSuccess() : bool
	{
		return $this->success;
	}

	public function getError() : string
	{
		return $this->error;
	}

	public function getTestName() : string
	{
		return $this->testName;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->success = $in->getBool();
		$this->error = $in->getString();
		$this->testName = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->success);
		$out->putString($this->error);
		$out->putString($this->testName);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleGameTestResults($this);
	}
}
