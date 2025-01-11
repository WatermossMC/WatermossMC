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

namespace watermossmc\player\chat;

use function str_replace;

/**
 * Legacy raw string chat formatter with the same behaviour as the old PlayerChatEvent::setFormat() API.
 * The format string should contain the placeholders {%0} and {%1} for the username and message respectively.
 */
final class LegacyRawChatFormatter implements ChatFormatter
{
	public function __construct(
		private string $format
	) {
	}

	public function format(string $username, string $message) : string
	{
		return str_replace(["{%0}", "{%1}"], [$username, $message], $this->format);
	}
}
