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

namespace watermossmc\network\mcpe\handler;

use watermossmc\network\mcpe\protocol\PacketHandlerDefaultImplTrait;
use watermossmc\network\mcpe\protocol\PacketHandlerInterface;

/**
 * Handlers are attached to sessions to handle packets received from their associated clients. A handler
 * is mutable and may be removed/replaced at any time.
 *
 * This class is an automatically generated stub. Do not edit it manually.
 */
abstract class PacketHandler implements PacketHandlerInterface
{
	use PacketHandlerDefaultImplTrait;

	public function setUp() : void
	{

	}
}
