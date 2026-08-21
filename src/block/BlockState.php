<?php

declare(strict_types=1);

namespace watermossmc\block;

final class BlockState
{
	/**
	 * @param array<string, int|string|bool> $states
	 */
	public function __construct(
		public readonly string $name,
		public readonly array $states = []
	) {}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array<string, int|string|bool>
	 */
	public function getStates(): array
	{
		return $this->states;
	}

	public function getKey(): string
	{
		$states = $this->states;
		ksort($states);

		return $this->name . ':' . json_encode(
			$states,
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
	}
}