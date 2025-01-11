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

class ServerboundDiagnosticsPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_DIAGNOSTICS_PACKET;

	private float $avgFps;
	private float $avgServerSimTickTimeMS;
	private float $avgClientSimTickTimeMS;
	private float $avgBeginFrameTimeMS;
	private float $avgInputTimeMS;
	private float $avgRenderTimeMS;
	private float $avgEndFrameTimeMS;
	private float $avgRemainderTimePercent;
	private float $avgUnaccountedTimePercent;

	/**
	 * @generate-create-func
	 */
	public static function create(
		float $avgFps,
		float $avgServerSimTickTimeMS,
		float $avgClientSimTickTimeMS,
		float $avgBeginFrameTimeMS,
		float $avgInputTimeMS,
		float $avgRenderTimeMS,
		float $avgEndFrameTimeMS,
		float $avgRemainderTimePercent,
		float $avgUnaccountedTimePercent,
	) : self {
		$result = new self();
		$result->avgFps = $avgFps;
		$result->avgServerSimTickTimeMS = $avgServerSimTickTimeMS;
		$result->avgClientSimTickTimeMS = $avgClientSimTickTimeMS;
		$result->avgBeginFrameTimeMS = $avgBeginFrameTimeMS;
		$result->avgInputTimeMS = $avgInputTimeMS;
		$result->avgRenderTimeMS = $avgRenderTimeMS;
		$result->avgEndFrameTimeMS = $avgEndFrameTimeMS;
		$result->avgRemainderTimePercent = $avgRemainderTimePercent;
		$result->avgUnaccountedTimePercent = $avgUnaccountedTimePercent;
		return $result;
	}

	public function getAvgFps() : float
	{
		return $this->avgFps;
	}

	public function getAvgServerSimTickTimeMS() : float
	{
		return $this->avgServerSimTickTimeMS;
	}

	public function getAvgClientSimTickTimeMS() : float
	{
		return $this->avgClientSimTickTimeMS;
	}

	public function getAvgBeginFrameTimeMS() : float
	{
		return $this->avgBeginFrameTimeMS;
	}

	public function getAvgInputTimeMS() : float
	{
		return $this->avgInputTimeMS;
	}

	public function getAvgRenderTimeMS() : float
	{
		return $this->avgRenderTimeMS;
	}

	public function getAvgEndFrameTimeMS() : float
	{
		return $this->avgEndFrameTimeMS;
	}

	public function getAvgRemainderTimePercent() : float
	{
		return $this->avgRemainderTimePercent;
	}

	public function getAvgUnaccountedTimePercent() : float
	{
		return $this->avgUnaccountedTimePercent;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->avgFps = $in->getLFloat();
		$this->avgServerSimTickTimeMS = $in->getLFloat();
		$this->avgClientSimTickTimeMS = $in->getLFloat();
		$this->avgBeginFrameTimeMS = $in->getLFloat();
		$this->avgInputTimeMS = $in->getLFloat();
		$this->avgRenderTimeMS = $in->getLFloat();
		$this->avgEndFrameTimeMS = $in->getLFloat();
		$this->avgRemainderTimePercent = $in->getLFloat();
		$this->avgUnaccountedTimePercent = $in->getLFloat();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLFloat($this->avgFps);
		$out->putLFloat($this->avgServerSimTickTimeMS);
		$out->putLFloat($this->avgClientSimTickTimeMS);
		$out->putLFloat($this->avgBeginFrameTimeMS);
		$out->putLFloat($this->avgInputTimeMS);
		$out->putLFloat($this->avgRenderTimeMS);
		$out->putLFloat($this->avgEndFrameTimeMS);
		$out->putLFloat($this->avgRemainderTimePercent);
		$out->putLFloat($this->avgUnaccountedTimePercent);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleServerboundDiagnostics($this);
	}
}
