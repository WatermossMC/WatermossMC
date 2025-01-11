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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackrequest;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStack;

use function count;

/**
 * Not clear what this is needed for, but it is very clearly marked as deprecated, so hopefully it'll go away before I
 * have to write a proper description for it.
 */
final class DeprecatedCraftingResultsStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING;

	/**
	 * @param ItemStack[] $results
	 */
	public function __construct(
		private array $results,
		private int $iterations
	) {
	}

	/** @return ItemStack[] */
	public function getResults() : array
	{
		return $this->results;
	}

	public function getIterations() : int
	{
		return $this->iterations;
	}

	public static function read(PacketSerializer $in) : self
	{
		$results = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$results[] = $in->getItemStackWithoutStackId();
		}
		$iterations = $in->getByte();
		return new self($results, $iterations);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->results));
		foreach ($this->results as $result) {
			$out->putItemStackWithoutStackId($result);
		}
		$out->putByte($this->iterations);
	}
}
