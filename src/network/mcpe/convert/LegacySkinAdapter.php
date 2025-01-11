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

namespace watermossmc\network\mcpe\convert;

use watermossmc\entity\InvalidSkinException;
use watermossmc\entity\Skin;
use watermossmc\network\mcpe\protocol\types\skin\SkinData;
use watermossmc\network\mcpe\protocol\types\skin\SkinImage;

use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function random_bytes;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

class LegacySkinAdapter implements SkinAdapter
{
	public function toSkinData(Skin $skin) : SkinData
	{
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if ($geometryName === "") {
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			SkinImage::fromLegacy($skin->getSkinData()),
			[],
			$capeImage,
			$skin->getGeometryData()
		);
	}

	public function fromSkinData(SkinData $data) : Skin
	{
		if ($data->isPersona()) {
			return new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 4096));
		}

		$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
			$geometryName = $resourcePatch["geometry"]["default"];
		} else {
			throw new InvalidSkinException("Missing geometry name field");
		}

		return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
	}
}
