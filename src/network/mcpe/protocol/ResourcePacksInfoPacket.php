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

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;

use function count;

class ResourcePacksInfoPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACKS_INFO_PACKET;

	/** @var ResourcePackInfoEntry[] */
	public array $resourcePackEntries = [];
	public bool $mustAccept = false; //if true, forces client to choose between accepting packs or being disconnected
	public bool $hasAddons = false;
	public bool $hasScripts = false; //if true, causes disconnect for any platform that doesn't support scripts yet
	private UuidInterface $worldTemplateId;
	private string $worldTemplateVersion;

	/**
	 * @generate-create-func
	 * @param ResourcePackInfoEntry[] $resourcePackEntries
	 */
	public static function create(array $resourcePackEntries, bool $mustAccept, bool $hasAddons, bool $hasScripts, UuidInterface $worldTemplateId, string $worldTemplateVersion) : self
	{
		$result = new self();
		$result->resourcePackEntries = $resourcePackEntries;
		$result->mustAccept = $mustAccept;
		$result->hasAddons = $hasAddons;
		$result->hasScripts = $hasScripts;
		$result->worldTemplateId = $worldTemplateId;
		$result->worldTemplateVersion = $worldTemplateVersion;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->mustAccept = $in->getBool();
		$this->hasAddons = $in->getBool();
		$this->hasScripts = $in->getBool();
		$this->worldTemplateId = $in->getUUID();
		$this->worldTemplateVersion = $in->getString();

		$resourcePackCount = $in->getLShort();
		while ($resourcePackCount-- > 0) {
			$this->resourcePackEntries[] = ResourcePackInfoEntry::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->mustAccept);
		$out->putBool($this->hasAddons);
		$out->putBool($this->hasScripts);
		$out->putUUID($this->worldTemplateId);
		$out->putString($this->worldTemplateVersion);
		$out->putLShort(count($this->resourcePackEntries));
		foreach ($this->resourcePackEntries as $entry) {
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePacksInfo($this);
	}
}
