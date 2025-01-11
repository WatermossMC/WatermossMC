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

namespace watermossmc\plugin;

use PHPUnit\Framework\TestCase;

use function sort;

class ApiVersionTest extends TestCase
{
	/**
	 * @return \Generator|mixed[][]
	 * @phpstan-return \Generator<int, array{string, string, bool}, void, void>
	 */
	public static function compatibleApiProvider() : \Generator
	{
		yield ["3.0.0", "3.0.0", true];
		yield ["3.1.0", "3.0.0", true];
		yield ["3.0.0", "3.1.0", false];
		yield ["3.1.0", "3.0.1", true]; //old bug where minor wasn't respected when comparing patches
		yield ["3.0.0", "4.0.0", false];
		yield ["4.0.0", "3.0.0", false];
		yield ["3.0.0", "3.0.1", false]; //bug fix patch required
		yield ["3.0.1", "3.0.0", true];
		yield ["3.0.0-ALPHA1", "3.0.0-ALPHA2", true];
		yield ["3.0.0-ALPHA2", "3.0.0-ALPHA1", true]; //at the time these weren't actually compatible, but these are just test samples.
		yield ["3.0.0-ALPHA1", "3.0.0-ALPHA1", true];
		yield ["3.0.0-ALPHA1", "4.0.0-ALPHA1", false];
	}

	/**
	 * @dataProvider compatibleApiProvider
	 */
	public function testCompatibleApi(string $myVersion, string $wantVersion, bool $expected) : void
	{
		self::assertSame($expected, ApiVersion::isCompatible($myVersion, [$wantVersion]), "my version: $myVersion, their version: $wantVersion, expect " . ($expected ? "yes" : "no"));
	}

	/**
	 * @return mixed[][][]
	 * @phpstan-return \Generator<int, array{list<string>, list<string>}, void, void>
	 */
	public static function ambiguousVersionsProvider() : \Generator
	{
		yield [["3.0.0"], []];
		yield [["3.0.0", "3.0.1"], ["3.0.0", "3.0.1"]];
		yield [["3.0.0", "3.1.0", "4.0.0"], ["3.0.0", "3.1.0"]];
		yield [["3.0.0", "4.0.0"], []];
		yield [["3.0.0-ALPHA1", "3.0.0-ALPHA2"], []];
	}

	/**
	 * @dataProvider ambiguousVersionsProvider
	 *
	 * @param string[] $input
	 * @param string[] $expectedOutput
	 */
	public function testFindAmbiguousVersions(array $input, array $expectedOutput) : void
	{
		$ambiguous = ApiVersion::checkAmbiguousVersions($input);

		sort($expectedOutput);
		sort($ambiguous);

		self::assertSame($expectedOutput, $ambiguous);
	}
}
