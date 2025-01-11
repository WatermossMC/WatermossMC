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

namespace watermossmc\phpstan\rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeWithClassName;
use watermossmc\utils\LegacyEnumShimTrait;

use function sprintf;

/**
 * @phpstan-implements Rule<StaticCall>
 */
final class DeprecatedLegacyEnumAccessRule implements Rule
{
	public function getNodeType() : string
	{
		return StaticCall::class;
	}

	public function processNode(Node $node, Scope $scope) : array
	{
		/** @var StaticCall $node */
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}
		$caseName = $node->name->name;
		$classType = $node->class instanceof Node\Name ?
			$scope->resolveTypeByName($node->class) :
			$scope->getType($node->class);

		if (!$classType instanceof TypeWithClassName) {
			return [];
		}

		$reflection = $classType->getClassReflection();
		if ($reflection === null || !$reflection->hasTraitUse(LegacyEnumShimTrait::class) || !$reflection->implementsInterface(\UnitEnum::class)) {
			return [];
		}

		if (!$reflection->hasNativeMethod($caseName)) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Use of legacy enum case accessor %s::%s().',
					$reflection->getName(),
					$caseName
				))->tip(sprintf(
					'Access the enum constant directly instead (remove the brackets), e.g. %s::%s',
					$reflection->getName(),
					$caseName
				))->build()
			];
		}

		return [];
	}
}
