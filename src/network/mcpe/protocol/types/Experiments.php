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

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

final class Experiments
{
	/**
	 * @param bool[] $experiments
	 * @phpstan-param array<string, bool> $experiments
	 */
	public function __construct(
		private array $experiments,
		private bool $hasPreviouslyUsedExperiments
	) {
	}

	/** @return bool[] */
	public function getExperiments() : array
	{
		return $this->experiments;
	}

	public function hasPreviouslyUsedExperiments() : bool
	{
		return $this->hasPreviouslyUsedExperiments;
	}

	public static function read(PacketSerializer $in) : self
	{
		$experiments = [];
		for ($i = 0, $len = $in->getLInt(); $i < $len; ++$i) {
			$experimentName = $in->getString();
			$enabled = $in->getBool();
			$experiments[$experimentName] = $enabled;
		}
		$hasPreviouslyUsedExperiments = $in->getBool();
		return new self($experiments, $hasPreviouslyUsedExperiments);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLInt(count($this->experiments));
		foreach ($this->experiments as $experimentName => $enabled) {
			$out->putString($experimentName);
			$out->putBool($enabled);
		}
		$out->putBool($this->hasPreviouslyUsedExperiments);
	}
}
