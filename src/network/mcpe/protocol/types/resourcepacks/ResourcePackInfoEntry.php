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

namespace watermossmc\network\mcpe\protocol\types\resourcepacks;

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class ResourcePackInfoEntry
{
	public function __construct(
		private UuidInterface $packId,
		private string $version,
		private int $sizeBytes,
		private string $encryptionKey = "",
		private string $subPackName = "",
		private string $contentId = "",
		private bool $hasScripts = false,
		private bool $isAddonPack = false,
		private bool $isRtxCapable = false,
		private string $cdnUrl = ""
	) {
	}

	public function getPackId() : UuidInterface
	{
		return $this->packId;
	}

	public function getVersion() : string
	{
		return $this->version;
	}

	public function getSizeBytes() : int
	{
		return $this->sizeBytes;
	}

	public function getEncryptionKey() : string
	{
		return $this->encryptionKey;
	}

	public function getSubPackName() : string
	{
		return $this->subPackName;
	}

	public function getContentId() : string
	{
		return $this->contentId;
	}

	public function hasScripts() : bool
	{
		return $this->hasScripts;
	}

	public function isAddonPack() : bool
	{
		return $this->isAddonPack;
	}

	public function isRtxCapable() : bool
	{
		return $this->isRtxCapable;
	}

	public function getCdnUrl() : string
	{
		return $this->cdnUrl;
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putUUID($this->packId);
		$out->putString($this->version);
		$out->putLLong($this->sizeBytes);
		$out->putString($this->encryptionKey);
		$out->putString($this->subPackName);
		$out->putString($this->contentId);
		$out->putBool($this->hasScripts);
		$out->putBool($this->isAddonPack);
		$out->putBool($this->isRtxCapable);
		$out->putString($this->cdnUrl);
	}

	public static function read(PacketSerializer $in) : self
	{
		$uuid = $in->getUUID();
		$version = $in->getString();
		$sizeBytes = $in->getLLong();
		$encryptionKey = $in->getString();
		$subPackName = $in->getString();
		$contentId = $in->getString();
		$hasScripts = $in->getBool();
		$isAddonPack = $in->getBool();
		$rtxCapable = $in->getBool();
		$cdnUrl = $in->getString();
		return new self($uuid, $version, $sizeBytes, $encryptionKey, $subPackName, $contentId, $hasScripts, $isAddonPack, $rtxCapable, $cdnUrl);
	}
}
