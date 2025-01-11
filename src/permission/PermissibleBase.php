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

namespace watermossmc\permission;

final class PermissibleBase implements Permissible
{
	use PermissibleDelegateTrait;

	private PermissibleInternal $permissibleBase;

	/**
	 * @param bool[] $basePermissions
	 * @phpstan-param array<string, bool> $basePermissions
	 */
	public function __construct(array $basePermissions)
	{
		$this->permissibleBase = new PermissibleInternal($basePermissions);
		$this->perm = $this->permissibleBase;
	}

	public function __destruct()
	{
		//permission subscriptions need to be cleaned up explicitly
		$this->permissibleBase->destroyCycles();
	}
}
