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

final class PlayerAction
{
	private function __construct()
	{
		//NOOP
	}

	public const START_BREAK = 0;
	public const ABORT_BREAK = 1;
	public const STOP_BREAK = 2;
	public const GET_UPDATED_BLOCK = 3;
	public const DROP_ITEM = 4;
	public const START_SLEEPING = 5;
	public const STOP_SLEEPING = 6;
	public const RESPAWN = 7;
	public const JUMP = 8;
	public const START_SPRINT = 9;
	public const STOP_SPRINT = 10;
	public const START_SNEAK = 11;
	public const STOP_SNEAK = 12;
	public const CREATIVE_PLAYER_DESTROY_BLOCK = 13;
	public const DIMENSION_CHANGE_ACK = 14; //sent when spawning in a different dimension to tell the server we spawned
	public const START_GLIDE = 15;
	public const STOP_GLIDE = 16;
	public const BUILD_DENIED = 17;
	public const CRACK_BLOCK = 18;
	public const CHANGE_SKIN = 19;
	public const SET_ENCHANTMENT_SEED = 20; //no longer used
	public const START_SWIMMING = 21;
	public const STOP_SWIMMING = 22;
	public const START_SPIN_ATTACK = 23;
	public const STOP_SPIN_ATTACK = 24;
	public const INTERACT_BLOCK = 25;
	public const PREDICT_DESTROY_BLOCK = 26;
	public const CONTINUE_DESTROY_BLOCK = 27;
	public const START_ITEM_USE_ON = 28;
	public const STOP_ITEM_USE_ON = 29;
	public const HANDLED_TELEPORT = 30;
	public const MISSED_SWING = 31;
	public const START_CRAWLING = 32;
	public const STOP_CRAWLING = 33;
	public const START_FLYING = 34;
	public const STOP_FLYING = 35;
	public const ACK_ACTOR_DATA = 36;
	public const START_USING_ITEM = 37;

	//Backwards compatibility (blame @dktapps)
	public const CRACK_BREAK = 18;
}
