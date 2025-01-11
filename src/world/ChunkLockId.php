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

namespace watermossmc\world;

use watermossmc\utils\NotCloneable;
use watermossmc\utils\NotSerializable;

/**
 * Represents a unique lock ID for use with World chunk locking.
 *
 * @see World::lockChunk()
 * @see World::unlockChunk()
 */
final class ChunkLockId
{
	use NotCloneable;
	use NotSerializable;
}
