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

/**
 * If you want to keep chunks loaded, implement this interface and register it into World.
 *
 * @see World::registerChunkLoader()
 * @see World::unregisterChunkLoader()
 *
 * WARNING: When moving this object around in the world or destroying it,
 * be sure to unregister the loader from chunks you're not using, otherwise you'll leak memory.
 */
interface ChunkLoader
{
}
