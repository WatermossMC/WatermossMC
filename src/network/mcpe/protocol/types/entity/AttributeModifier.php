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

namespace watermossmc\network\mcpe\protocol\types\entity;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class AttributeModifier
{
	/**
	 * @see AttributeModifierOperation
	 * @see AttributeModifierTargetOperand
	 */
	public function __construct(
		private string $id,
		private string $name,
		private float $amount,
		private int $operation,
		private int $operand,
		private bool $serializable //???
	) {
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getAmount() : float
	{
		return $this->amount;
	}

	public function getOperation() : int
	{
		return $this->operation;
	}

	public function getOperand() : int
	{
		return $this->operand;
	}

	public function isSerializable() : bool
	{
		return $this->serializable;
	}

	public static function read(PacketSerializer $in) : self
	{
		$id = $in->getString();
		$name = $in->getString();
		$amount = $in->getLFloat();
		$operation = $in->getLInt();
		$operand = $in->getLInt();
		$serializable = $in->getBool();

		return new self($id, $name, $amount, $operation, $operand, $serializable);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->id);
		$out->putString($this->name);
		$out->putLFloat($this->amount);
		$out->putLInt($this->operation);
		$out->putLInt($this->operand);
		$out->putBool($this->serializable);
	}
}
