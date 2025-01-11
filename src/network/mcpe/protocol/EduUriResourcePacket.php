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
use watermossmc\network\mcpe\protocol\types\EducationUriResource;

class EduUriResourcePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::EDU_URI_RESOURCE_PACKET;

	private EducationUriResource $resource;

	/**
	 * @generate-create-func
	 */
	public static function create(EducationUriResource $resource) : self
	{
		$result = new self();
		$result->resource = $resource;
		return $result;
	}

	public function getResource() : EducationUriResource
	{
		return $this->resource;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->resource = EducationUriResource::read($in);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->resource->write($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleEduUriResource($this);
	}
}
