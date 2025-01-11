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

namespace watermossmc\math;

use function implode;
use function max;
use function substr;

/**
 * @phpstan-implements \ArrayAccess<int, float[]>
 */
class Matrix implements \ArrayAccess
{
	/** @var float[][] */
	private array $matrix = [];
	private int $rows;
	private int $columns;

	public function offsetExists($offset) : bool
	{
		return isset($this->matrix[(int) $offset]);
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->matrix[(int) $offset];
	}

	public function offsetSet($offset, $value) : void
	{
		$this->matrix[(int) $offset] = $value;
	}

	public function offsetUnset($offset) : void
	{
		unset($this->matrix[(int) $offset]);
	}

	/**
	 * @param float[][] $set
	 */
	public function __construct(int $rows, int $columns, array $set = [])
	{
		$this->rows = max(1, $rows);
		$this->columns = max(1, $columns);
		$this->set($set);
	}

	/**
	 * @param float[][] $m
	 */
	public function set(array $m) : void
	{
		for ($r = 0; $r < $this->rows; ++$r) {
			$this->matrix[$r] = [];
			for ($c = 0; $c < $this->columns; ++$c) {
				$this->matrix[$r][$c] = $m[$r][$c] ?? 0;
			}
		}
	}

	public function getRows() : int
	{
		return $this->rows;
	}

	public function getColumns() : int
	{
		return $this->columns;
	}

	public function setElement(int $row, int $column, float $value) : void
	{
		if ($row > $this->rows || $row < 0 || $column > $this->columns || $column < 0) {
			throw new \InvalidArgumentException("Row or column out of bounds (have $this->rows rows $this->columns columns)");
		}
		$this->matrix[$row][$column] = $value;
	}

	public function getElement(int $row, int $column) : float
	{
		if ($row > $this->rows || $row < 0 || $column > $this->columns || $column < 0) {
			throw new \InvalidArgumentException("Row or column out of bounds (have $this->rows rows $this->columns columns)");
		}

		return $this->matrix[$row][$column];
	}

	public function isSquare() : bool
	{
		return $this->rows === $this->columns;
	}

	public function add(Matrix $matrix) : Matrix
	{
		if ($this->rows !== $matrix->getRows() || $this->columns !== $matrix->getColumns()) {
			throw new \InvalidArgumentException("Matrix does not have the same number of rows and/or columns");
		}
		$result = new Matrix($this->rows, $this->columns);
		for ($r = 0; $r < $this->rows; ++$r) {
			for ($c = 0; $c < $this->columns; ++$c) {
				$element = $matrix->getElement($r, $c);
				$result->setElement($r, $c, $this->matrix[$r][$c] + $element);
			}
		}

		return $result;
	}

	public function subtract(Matrix $matrix) : Matrix
	{
		if ($this->rows !== $matrix->getRows() || $this->columns !== $matrix->getColumns()) {
			throw new \InvalidArgumentException("Matrix does not have the same number of rows and/or columns");
		}
		$result = clone $this;
		for ($r = 0; $r < $this->rows; ++$r) {
			for ($c = 0; $c < $this->columns; ++$c) {
				$element = $matrix->getElement($r, $c);
				$result->setElement($r, $c, $this->matrix[$r][$c] - $element);
			}
		}

		return $result;
	}

	public function multiplyScalar(float $number) : Matrix
	{
		$result = clone $this;
		for ($r = 0; $r < $this->rows; ++$r) {
			for ($c = 0; $c < $this->columns; ++$c) {
				$result->setElement($r, $c, $this->matrix[$r][$c] * $number);
			}
		}

		return $result;
	}

	public function divideScalar(float $number) : Matrix
	{
		$result = clone $this;
		for ($r = 0; $r < $this->rows; ++$r) {
			for ($c = 0; $c < $this->columns; ++$c) {
				$result->setElement($r, $c, $this->matrix[$r][$c] / $number);
			}
		}

		return $result;
	}

	public function transpose() : Matrix
	{
		$result = new Matrix($this->columns, $this->rows);
		for ($r = 0; $r < $this->rows; ++$r) {
			for ($c = 0; $c < $this->columns; ++$c) {
				$result->setElement($c, $r, $this->matrix[$r][$c]);
			}
		}

		return $result;
	}

	/**
	 * Naive Matrix product, O(n^3)
	 */
	public function product(Matrix $matrix) : Matrix
	{
		if ($this->columns !== $matrix->getRows()) {
			throw new \InvalidArgumentException("Expected a matrix with $this->columns rows"); //????
		}
		$c = $matrix->getColumns();
		$result = new Matrix($this->rows, $c);
		for ($i = 0; $i < $this->rows; ++$i) {
			for ($j = 0; $j < $c; ++$j) {
				$sum = 0;
				for ($k = 0; $k < $this->columns; ++$k) {
					$sum += $this->matrix[$i][$k] * $matrix->getElement($k, $j);
				}
				$result->setElement($i, $j, $sum);
			}
		}

		return $result;
	}

	/**
	 * Computation of the determinant of 1x1, 2x2 and 3x3 matrices
	 */
	public function determinant() : float
	{
		if ($this->isSquare() !== true) {
			throw new \LogicException("Cannot calculate determinant of a non-square matrix");
		}
		return match($this->rows) {
			1 => $this->matrix[0][0],
			2 =>
				$this->matrix[0][0] * $this->matrix[1][1] -
				$this->matrix[0][1] * $this->matrix[1][0],
			3 =>
				$this->matrix[0][0] * $this->matrix[1][1] * $this->matrix[2][2] +
				$this->matrix[0][1] * $this->matrix[1][2] * $this->matrix[2][0] +
				$this->matrix[0][2] * $this->matrix[1][0] * $this->matrix[2][1] -
				$this->matrix[2][0] * $this->matrix[1][1] * $this->matrix[0][2] -
				$this->matrix[2][1] * $this->matrix[1][2] * $this->matrix[0][0] -
				$this->matrix[2][2] * $this->matrix[1][0] * $this->matrix[0][1],
			default => throw new \LogicException("Not implemented")
		};
	}

	public function __toString() : string
	{
		$s = "";
		for ($r = 0; $r < $this->rows; ++$r) {
			$s .= implode(",", $this->matrix[$r]) . ";";
		}

		return "Matrix({$this->rows}x{$this->columns};" . substr($s, 0, -1) . ")";
	}

}
