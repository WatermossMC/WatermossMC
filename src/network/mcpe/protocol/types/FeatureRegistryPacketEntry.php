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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class FeatureRegistryPacketEntry
{
	public function __construct(
		private string $featureName,
		private string $featureJson
	) {
	}

	public function getFeatureName() : string
	{
		return $this->featureName;
	}

	public function getFeatureJson() : string
	{
		return $this->featureJson;
	}

	public static function read(PacketSerializer $in) : self
	{
		$featureName = $in->getString();
		$featureJson = $in->getString();

		return new self($featureName, $featureJson);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->featureName);
		$out->putString($this->featureJson);
	}
}
