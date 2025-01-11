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
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @phpstan-implements Rule<Foreach_>
 */
final class DisallowForeachByReferenceRule implements Rule
{
	public function getNodeType() : string
	{
		return Foreach_::class;
	}

	public function processNode(Node $node, Scope $scope) : array
	{
		/** @var Foreach_ $node */
		if ($node->byRef) {
			return [
				RuleErrorBuilder::message("Foreach by-reference is not allowed, because it has surprising behaviour.")
					->tip("If the value variable is used outside of the foreach construct (e.g. in a second foreach), the iterable's contents will be unexpectedly altered.")
					->build()
			];
		}

		return [];
	}
}
