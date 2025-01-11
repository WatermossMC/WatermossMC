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

interface Permissible
{
	/**
	 * Assigns a baseline permission to the permissible. This is **always** calculated before anything else, which means
	 * that permissions set using addAttachment() will always override base permissions.
	 * You probably don't want to use this if you're not assigning (denying) operator permissions.
	 *
	 * @internal
	 * @see Permissible::addAttachment() for normal permission assignments
	 */
	public function setBasePermission(Permission|string $name, bool $grant) : void;

	/**
	 * Unsets a baseline permission previously set. If it wasn't already set, this will have no effect.
	 * Note that this might have different results than setting the permission to false.
	 *
	 * @internal
	 */
	public function unsetBasePermission(Permission|string $name) : void;

	/**
	 * Checks if this instance has a permission overridden
	 */
	public function isPermissionSet(Permission|string $name) : bool;

	/**
	 * Returns the permission value if overridden, or the default value if not
	 */
	public function hasPermission(Permission|string $name) : bool;

	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment;

	public function removeAttachment(PermissionAttachment $attachment) : void;

	/**
	 * @return bool[] changed permission name => old value
	 * @phpstan-return array<string, bool>
	 */
	public function recalculatePermissions() : array;

	/**
	 * @return ObjectSet|\Closure[]
	 * @phpstan-return ObjectSet<\Closure(array<string, bool> $changedPermissionsOldValues) : void>
	 */
	public function getPermissionRecalculationCallbacks() : ObjectSet;

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array;

}
