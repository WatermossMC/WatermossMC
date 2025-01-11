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

use watermossmc\network\mcpe\protocol\types\skin\PersonaPieceTintColor;
use watermossmc\network\mcpe\protocol\types\skin\PersonaSkinPiece;
use watermossmc\network\mcpe\protocol\types\skin\SkinAnimation;
use watermossmc\network\mcpe\protocol\types\skin\SkinData;
use watermossmc\network\mcpe\protocol\types\skin\SkinImage;

use function array_map;
use function base64_decode;

final class ClientDataToSkinDataHelper
{
	/**
	 * @throws \InvalidArgumentException
	 */
	private static function safeB64Decode(string $base64, string $context) : string
	{
		$result = base64_decode($base64, true);
		if ($result === false) {
			throw new \InvalidArgumentException("$context: Malformed base64, cannot be decoded");
		}
		return $result;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function fromClientData(ClientData $clientData) : SkinData
	{
		/** @var SkinAnimation[] $animations */
		$animations = [];
		foreach ($clientData->AnimatedImageData as $k => $animation) {
			$animations[] = new SkinAnimation(
				new SkinImage(
					$animation->ImageHeight,
					$animation->ImageWidth,
					self::safeB64Decode($animation->Image, "AnimatedImageData.$k.Image")
				),
				$animation->Type,
				$animation->Frames,
				$animation->AnimationExpression
			);
		}
		return new SkinData(
			$clientData->SkinId,
			$clientData->PlayFabId,
			self::safeB64Decode($clientData->SkinResourcePatch, "SkinResourcePatch"),
			new SkinImage($clientData->SkinImageHeight, $clientData->SkinImageWidth, self::safeB64Decode($clientData->SkinData, "SkinData")),
			$animations,
			new SkinImage($clientData->CapeImageHeight, $clientData->CapeImageWidth, self::safeB64Decode($clientData->CapeData, "CapeData")),
			self::safeB64Decode($clientData->SkinGeometryData, "SkinGeometryData"),
			self::safeB64Decode($clientData->SkinGeometryDataEngineVersion, "SkinGeometryDataEngineVersion"), //yes, they actually base64'd the version!
			self::safeB64Decode($clientData->SkinAnimationData, "SkinAnimationData"),
			$clientData->CapeId,
			null,
			$clientData->ArmSize,
			$clientData->SkinColor,
			array_map(function (ClientDataPersonaSkinPiece $piece) : PersonaSkinPiece {
				return new PersonaSkinPiece($piece->PieceId, $piece->PieceType, $piece->PackId, $piece->IsDefault, $piece->ProductId);
			}, $clientData->PersonaPieces),
			array_map(function (ClientDataPersonaPieceTintColor $tint) : PersonaPieceTintColor {
				return new PersonaPieceTintColor($tint->PieceType, $tint->Colors);
			}, $clientData->PieceTintColors),
			true,
			$clientData->PremiumSkin,
			$clientData->PersonaSkin,
			$clientData->CapeOnClassicSkin,
			true, //assume this is true? there's no field for it ...
			$clientData->OverrideSkin ?? true,
		);
	}
}
