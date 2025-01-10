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

namespace watermossmc\thread;

use pmmp\thread\Thread as NativeThread;
use watermossmc\scheduler\AsyncTask;

/**
 * Specialized Thread class aimed at WatermossMC-related usages. It handles setting up autoloading and error handling.
 *
 * Note: You probably don't need a thread unless you're doing something in it that's expected to last a long time (or
 * indefinitely).
 * For CPU-demanding tasks that take a short amount of time, consider using AsyncTasks instead to make better use of the
 * CPU.
 * @see AsyncTask
 */
abstract class Thread extends NativeThread
{
	use CommonThreadPartsTrait;
}
