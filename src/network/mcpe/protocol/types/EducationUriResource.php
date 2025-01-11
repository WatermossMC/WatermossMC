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

final class EducationUriResource
{
	public function __construct(
		private string $buttonName,
		private string $linkUri
	) {
	}

	public function getButtonName() : string
	{
		return $this->buttonName;
	}

	public function getLinkUri() : string
	{
		return $this->linkUri;
	}

	public static function read(PacketSerializer $in) : self
	{
		$buttonName = $in->getString();
		$linkUri = $in->getString();
		return new self($buttonName, $linkUri);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->buttonName);
		$out->putString($this->linkUri);
	}
}
