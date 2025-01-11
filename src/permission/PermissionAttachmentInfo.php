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

class PermissionAttachmentInfo
{
	public function __construct(
		private string $permission,
		private ?PermissionAttachment $attachment,
		private bool $value,
		private ?PermissionAttachmentInfo $groupPermission
	) {
	}

	public function getPermission() : string
	{
		return $this->permission;
	}

	public function getAttachment() : ?PermissionAttachment
	{
		return $this->attachment;
	}

	public function getValue() : bool
	{
		return $this->value;
	}

	/**
	 * Returns the info of the permission group that caused this permission to be set, if any.
	 * If null, the permission was set explicitly, either by a permission attachment or base permission.
	 */
	public function getGroupPermissionInfo() : ?PermissionAttachmentInfo
	{
		return $this->groupPermission;
	}
}
