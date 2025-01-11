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
use watermossmc\network\mcpe\protocol\types\Experiments;
use watermossmc\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;

use function count;

class ResourcePackStackPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_STACK_PACKET;

	/** @var ResourcePackStackEntry[] */
	public array $resourcePackStack = [];
	/** @var ResourcePackStackEntry[] */
	public array $behaviorPackStack = [];
	public bool $mustAccept = false;
	public string $baseGameVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	public Experiments $experiments;
	public bool $useVanillaEditorPacks;

	/**
	 * @generate-create-func
	 * @param ResourcePackStackEntry[] $resourcePackStack
	 * @param ResourcePackStackEntry[] $behaviorPackStack
	 */
	public static function create(array $resourcePackStack, array $behaviorPackStack, bool $mustAccept, string $baseGameVersion, Experiments $experiments, bool $useVanillaEditorPacks) : self
	{
		$result = new self();
		$result->resourcePackStack = $resourcePackStack;
		$result->behaviorPackStack = $behaviorPackStack;
		$result->mustAccept = $mustAccept;
		$result->baseGameVersion = $baseGameVersion;
		$result->experiments = $experiments;
		$result->useVanillaEditorPacks = $useVanillaEditorPacks;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->mustAccept = $in->getBool();
		$behaviorPackCount = $in->getUnsignedVarInt();
		while ($behaviorPackCount-- > 0) {
			$this->behaviorPackStack[] = ResourcePackStackEntry::read($in);
		}

		$resourcePackCount = $in->getUnsignedVarInt();
		while ($resourcePackCount-- > 0) {
			$this->resourcePackStack[] = ResourcePackStackEntry::read($in);
		}

		$this->baseGameVersion = $in->getString();
		$this->experiments = Experiments::read($in);
		$this->useVanillaEditorPacks = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->mustAccept);

		$out->putUnsignedVarInt(count($this->behaviorPackStack));
		foreach ($this->behaviorPackStack as $entry) {
			$entry->write($out);
		}

		$out->putUnsignedVarInt(count($this->resourcePackStack));
		foreach ($this->resourcePackStack as $entry) {
			$entry->write($out);
		}

		$out->putString($this->baseGameVersion);
		$this->experiments->write($out);
		$out->putBool($this->useVanillaEditorPacks);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePackStack($this);
	}
}
