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

namespace watermossmc\plugin;

final class PluginDescriptionCommandEntry
{
	/**
	 * @param string[] $aliases
	 * @phpstan-param list<string> $aliases
	 */
	public function __construct(
		private ?string $description,
		private ?string $usageMessage,
		private array $aliases,
		private string $permission,
		private ?string $permissionDeniedMessage,
	) {
	}

	public function getDescription() : ?string
	{
		return $this->description;
	}

	public function getUsageMessage() : ?string
	{
		return $this->usageMessage;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases() : array
	{
		return $this->aliases;
	}

	public function getPermission() : string
	{
		return $this->permission;
	}

	public function getPermissionDeniedMessage() : ?string
	{
		return $this->permissionDeniedMessage;
	}
}
