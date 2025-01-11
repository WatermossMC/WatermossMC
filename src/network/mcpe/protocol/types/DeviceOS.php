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

final class DeviceOS
{
	public const UNKNOWN = -1;
	public const ANDROID = 1;
	public const IOS = 2;
	public const OSX = 3;
	public const AMAZON = 4;
	public const GEAR_VR = 5;
	public const HOLOLENS = 6;
	public const WINDOWS_10 = 7;
	public const WIN32 = 8;
	public const DEDICATED = 9;
	public const TVOS = 10;
	public const PLAYSTATION = 11;
	public const NINTENDO = 12;
	public const XBOX = 13;
	public const WINDOWS_PHONE = 14;

}
