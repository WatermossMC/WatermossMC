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

final class EducationSettingsExternalLinkSettings
{
	public function __construct(
		private string $url,
		private string $displayName
	) {
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function getDisplayName() : string
	{
		return $this->displayName;
	}

	public static function read(PacketSerializer $in) : self
	{
		$url = $in->getString();
		$displayName = $in->getString();
		return new self($displayName, $url);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->url);
		$out->putString($this->displayName);
	}
}
