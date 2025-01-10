<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\plugin;

use watermossmc\lang\KnownTranslationFactory;
use watermossmc\lang\Translatable;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\utils\Utils;
use watermossmc\utils\VersionString;

use function array_intersect;
use function count;
use function extension_loaded;
use function implode;
use function in_array;
use function phpversion;
use function str_starts_with;
use function stripos;
use function strlen;
use function substr;
use function version_compare;

final class PluginLoadabilityChecker
{
	public function __construct(
		private string $apiVersion
	) {
	}

	public function check(PluginDescription $description) : Translatable|null
	{
		$name = $description->getName();
		if (stripos($name, "watermossmc") !== false || stripos($name, "minecraft") !== false || stripos($name, "mojang") !== false) {
			return KnownTranslationFactory::watermossmc_plugin_restrictedName();
		}

		foreach ($description->getCompatibleApis() as $api) {
			if (!VersionString::isValidBaseVersion($api)) {
				return KnownTranslationFactory::watermossmc_plugin_invalidAPI($api);
			}
		}

		if (!ApiVersion::isCompatible($this->apiVersion, $description->getCompatibleApis())) {
			return KnownTranslationFactory::watermossmc_plugin_incompatibleAPI(implode(", ", $description->getCompatibleApis()));
		}

		$ambiguousVersions = ApiVersion::checkAmbiguousVersions($description->getCompatibleApis());
		if (count($ambiguousVersions) > 0) {
			return KnownTranslationFactory::watermossmc_plugin_ambiguousMinAPI(implode(", ", $ambiguousVersions));
		}

		if (count($description->getCompatibleOperatingSystems()) > 0 && !in_array(Utils::getOS(), $description->getCompatibleOperatingSystems(), true)) {
			return KnownTranslationFactory::watermossmc_plugin_incompatibleOS(implode(", ", $description->getCompatibleOperatingSystems()));
		}

		if (count($pluginMcpeProtocols = $description->getCompatibleMcpeProtocols()) > 0) {
			$serverMcpeProtocols = [ProtocolInfo::CURRENT_PROTOCOL];
			if (count(array_intersect($pluginMcpeProtocols, $serverMcpeProtocols)) === 0) {
				return KnownTranslationFactory::watermossmc_plugin_incompatibleProtocol(implode(", ", $pluginMcpeProtocols));
			}
		}

		foreach (Utils::stringifyKeys($description->getRequiredExtensions()) as $extensionName => $versionConstrs) {
			if (!extension_loaded($extensionName)) {
				return KnownTranslationFactory::watermossmc_plugin_extensionNotLoaded($extensionName);
			}
			$gotVersion = phpversion($extensionName);
			if ($gotVersion === false) {
				//extensions may set NULL as the extension version, in which case phpversion() may return false
				$gotVersion = "**UNKNOWN**";
			}

			foreach ($versionConstrs as $k => $constr) { // versionConstrs_loop
				if ($constr === "*") {
					continue;
				}
				if ($constr === "") {
					return KnownTranslationFactory::watermossmc_plugin_emptyExtensionVersionConstraint(extensionName: $extensionName, constraintIndex: "$k");
				}
				foreach (["<=", "le", "<>", "!=", "ne", "<", "lt", "==", "=", "eq", ">=", "ge", ">", "gt"] as $comparator) {
					// warning: the > character should be quoted in YAML
					if (str_starts_with($constr, $comparator)) {
						$version = substr($constr, strlen($comparator));
						if (!version_compare($gotVersion, $version, $comparator)) {
							return KnownTranslationFactory::watermossmc_plugin_incompatibleExtensionVersion(extensionName: $extensionName, extensionVersion: $gotVersion, pluginRequirement: $constr);
						}
						continue 2; // versionConstrs_loop
					}
				}
				return KnownTranslationFactory::watermossmc_plugin_invalidExtensionVersionConstraint(extensionName: $extensionName, versionConstraint: $constr);
			}
		}

		return null;
	}
}
