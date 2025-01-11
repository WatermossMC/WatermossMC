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

use watermossmc\plugin\Plugin;
use watermossmc\utils\ObjectSet;

trait PermissibleDelegateTrait
{
	/** @var Permissible */
	private $perm;

	public function setBasePermission(Permission|string $name, bool $grant) : void
	{
		$this->perm->setBasePermission($name, $grant);
	}

	public function unsetBasePermission(Permission|string $name) : void
	{
		$this->perm->unsetBasePermission($name);
	}

	public function isPermissionSet(Permission|string $name) : bool
	{
		return $this->perm->isPermissionSet($name);
	}

	public function hasPermission(Permission|string $name) : bool
	{
		return $this->perm->hasPermission($name);
	}

	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment
	{
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	public function removeAttachment(PermissionAttachment $attachment) : void
	{
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions() : array
	{
		return $this->perm->recalculatePermissions();
	}

	/**
	 * @return ObjectSet|\Closure[]
	 * @phpstan-return ObjectSet<\Closure(array<string, bool> $changedPermissionsOldValues) : void>
	 */
	public function getPermissionRecalculationCallbacks() : ObjectSet
	{
		return $this->perm->getPermissionRecalculationCallbacks();
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array
	{
		return $this->perm->getEffectivePermissions();
	}

}
