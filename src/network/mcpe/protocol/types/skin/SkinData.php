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

namespace watermossmc\network\mcpe\protocol\types\skin;

use Ramsey\Uuid\Uuid;
use watermossmc\network\mcpe\protocol\ProtocolInfo;

class SkinData
{
	public const ARM_SIZE_SLIM = "slim";
	public const ARM_SIZE_WIDE = "wide";

	private SkinImage $capeImage;
	private string $fullSkinId;

	/**
	 * @param SkinAnimation[]         $animations
	 * @param PersonaSkinPiece[]      $personaPieces
	 * @param PersonaPieceTintColor[] $pieceTintColors
	 */
	public function __construct(
		private string $skinId,
		private string $playFabId,
		private string $resourcePatch,
		private SkinImage $skinImage,
		private array $animations = [],
		?SkinImage $capeImage = null,
		private string $geometryData = "",
		private string $geometryDataEngineVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK,
		private string $animationData = "",
		private string $capeId = "",
		?string $fullSkinId = null,
		private string $armSize = self::ARM_SIZE_WIDE,
		private string $skinColor = "",
		private array $personaPieces = [],
		private array $pieceTintColors = [],
		private bool $isVerified = true,
		private bool $premium = false,
		private bool $persona = false,
		private bool $personaCapeOnClassic = false,
		private bool $isPrimaryUser = true,
		private bool $override = true
	) {
		$this->capeImage = $capeImage ?? new SkinImage(0, 0, "");
		//this has to be unique or the client will do stupid things
		$this->fullSkinId = $fullSkinId ?? Uuid::uuid4()->toString();
	}

	public function getSkinId() : string
	{
		return $this->skinId;
	}

	public function getPlayFabId() : string
	{
		return $this->playFabId;
	}

	public function getResourcePatch() : string
	{
		return $this->resourcePatch;
	}

	public function getSkinImage() : SkinImage
	{
		return $this->skinImage;
	}

	/**
	 * @return SkinAnimation[]
	 */
	public function getAnimations() : array
	{
		return $this->animations;
	}

	public function getCapeImage() : SkinImage
	{
		return $this->capeImage;
	}

	public function getGeometryData() : string
	{
		return $this->geometryData;
	}

	public function getGeometryDataEngineVersion() : string
	{
		return $this->geometryDataEngineVersion;
	}

	public function getAnimationData() : string
	{
		return $this->animationData;
	}

	public function getCapeId() : string
	{
		return $this->capeId;
	}

	public function getFullSkinId() : string
	{
		return $this->fullSkinId;
	}

	public function getArmSize() : string
	{
		return $this->armSize;
	}

	public function getSkinColor() : string
	{
		return $this->skinColor;
	}

	/**
	 * @return PersonaSkinPiece[]
	 */
	public function getPersonaPieces() : array
	{
		return $this->personaPieces;
	}

	/**
	 * @return PersonaPieceTintColor[]
	 */
	public function getPieceTintColors() : array
	{
		return $this->pieceTintColors;
	}

	public function isPersona() : bool
	{
		return $this->persona;
	}

	public function isPremium() : bool
	{
		return $this->premium;
	}

	public function isPersonaCapeOnClassic() : bool
	{
		return $this->personaCapeOnClassic;
	}

	public function isPrimaryUser() : bool
	{
		return $this->isPrimaryUser;
	}

	public function isOverride() : bool
	{
		return $this->override;
	}

	public function isVerified() : bool
	{
		return $this->isVerified;
	}

	/**
	 * @internal
	 */
	public function setVerified(bool $verified) : void
	{
		$this->isVerified = $verified;
	}
}
