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

namespace watermossmc\item;

use watermossmc\block\utils\RecordType;

class Record extends Item
{
	private RecordType $recordType;

	//TODO: inconsistent parameter order
	public function __construct(ItemIdentifier $identifier, RecordType $recordType, string $name)
	{
		$this->recordType = $recordType;
		parent::__construct($identifier, $name);
	}

	public function getRecordType() : RecordType
	{
		return $this->recordType;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}
}
