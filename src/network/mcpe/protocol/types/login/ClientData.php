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

namespace watermossmc\network\mcpe\protocol\types\login;

/**
 * Model class for LoginPacket JSON data for JsonMapper
 */
final class ClientData
{
	/**
	 * @var ClientDataAnimationFrame[]
	 * @required
	 */
	public array $AnimatedImageData;

	/** @required */
	public string $ArmSize;

	/** @required */
	public string $CapeData;

	/** @required */
	public string $CapeId;

	/** @required */
	public int $CapeImageHeight;

	/** @required */
	public int $CapeImageWidth;

	/** @required */
	public bool $CapeOnClassicSkin;

	/** @required */
	public int $ClientRandomId;

	/** @required */
	public bool $CompatibleWithClientSideChunkGen;

	/** @required */
	public int $CurrentInputMode;

	/** @required */
	public int $DefaultInputMode;

	/** @required */
	public string $DeviceId;

	/** @required */
	public string $DeviceModel;

	/** @required */
	public int $DeviceOS;

	/** @required */
	public string $GameVersion;

	/** @required */
	public int $GuiScale;

	/** @required */
	public bool $IsEditorMode;

	/** @required */
	public string $LanguageCode;

	/** @required */
	public int $MaxViewDistance;

	/** @required */
	public int $MemoryTier;

	public bool $OverrideSkin;

	/**
	 * @var ClientDataPersonaSkinPiece[]
	 * @required
	 */
	public array $PersonaPieces;

	/** @required */
	public bool $PersonaSkin;

	/**
	 * @var ClientDataPersonaPieceTintColor[]
	 * @required
	 */
	public array $PieceTintColors;

	/** @required */
	public string $PlatformOfflineId;

	/** @required */
	public string $PlatformOnlineId;

	/** @required */
	public int $PlatformType;

	public string $PlatformUserId = ""; //xbox-only, apparently

	/** @required */
	public string $PlayFabId;

	/** @required */
	public bool $PremiumSkin = false;

	/** @required */
	public string $SelfSignedId;

	/** @required */
	public string $ServerAddress;

	/** @required */
	public string $SkinAnimationData;

	/** @required */
	public string $SkinColor;

	/** @required */
	public string $SkinData;

	/** @required */
	public string $SkinGeometryData;

	/** @required */
	public string $SkinGeometryDataEngineVersion;

	/** @required */
	public string $SkinId;

	/** @required */
	public int $SkinImageHeight;

	/** @required */
	public int $SkinImageWidth;

	/** @required */
	public string $SkinResourcePatch;

	/** @required */
	public string $ThirdPartyName;

	/** @required */
	public bool $ThirdPartyNameOnly;

	/** @required */
	public bool $TrustedSkin;

	/** @required */
	public int $UIProfile;
}
