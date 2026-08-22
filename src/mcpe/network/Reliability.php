<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\mcpe\network;

final class Reliability
{
    public const UNRELIABLE = 0;
    public const UNRELIABLE_SEQUENCED = 1;
    public const RELIABLE = 2;
    public const RELIABLE_ORDERED = 3;
    public const RELIABLE_SEQUENCED = 4;
    public const UNRELIABLE_WITH_ACK_RECEIPT = 5;
    public const RELIABLE_WITH_ACK_RECEIPT = 6;
    public const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;
}
