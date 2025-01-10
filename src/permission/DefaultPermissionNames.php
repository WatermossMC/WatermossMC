<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

namespace watermossmc\permission;

final class DefaultPermissionNames
{
	public const BROADCAST_ADMIN = "watermossmc.broadcast.admin";
	public const BROADCAST_USER = "watermossmc.broadcast.user";
	public const COMMAND_BAN_IP = "watermossmc.command.ban.ip";
	public const COMMAND_BAN_LIST = "watermossmc.command.ban.list";
	public const COMMAND_BAN_PLAYER = "watermossmc.command.ban.player";
	public const COMMAND_CLEAR_OTHER = "watermossmc.command.clear.other";
	public const COMMAND_CLEAR_SELF = "watermossmc.command.clear.self";
	public const COMMAND_DEFAULTGAMEMODE = "watermossmc.command.defaultgamemode";
	public const COMMAND_DIFFICULTY = "watermossmc.command.difficulty";
	public const COMMAND_DUMPMEMORY = "watermossmc.command.dumpmemory";
	public const COMMAND_EFFECT_OTHER = "watermossmc.command.effect.other";
	public const COMMAND_EFFECT_SELF = "watermossmc.command.effect.self";
	public const COMMAND_ENCHANT_OTHER = "watermossmc.command.enchant.other";
	public const COMMAND_ENCHANT_SELF = "watermossmc.command.enchant.self";
	public const COMMAND_GAMEMODE_OTHER = "watermossmc.command.gamemode.other";
	public const COMMAND_GAMEMODE_SELF = "watermossmc.command.gamemode.self";
	public const COMMAND_GC = "watermossmc.command.gc";
	public const COMMAND_GIVE_OTHER = "watermossmc.command.give.other";
	public const COMMAND_GIVE_SELF = "watermossmc.command.give.self";
	public const COMMAND_HELP = "watermossmc.command.help";
	public const COMMAND_KICK = "watermossmc.command.kick";
	public const COMMAND_KILL_OTHER = "watermossmc.command.kill.other";
	public const COMMAND_KILL_SELF = "watermossmc.command.kill.self";
	public const COMMAND_LIST = "watermossmc.command.list";
	public const COMMAND_ME = "watermossmc.command.me";
	public const COMMAND_OP_GIVE = "watermossmc.command.op.give";
	public const COMMAND_OP_TAKE = "watermossmc.command.op.take";
	public const COMMAND_PARTICLE = "watermossmc.command.particle";
	public const COMMAND_PLUGINS = "watermossmc.command.plugins";
	public const COMMAND_SAVE_DISABLE = "watermossmc.command.save.disable";
	public const COMMAND_SAVE_ENABLE = "watermossmc.command.save.enable";
	public const COMMAND_SAVE_PERFORM = "watermossmc.command.save.perform";
	public const COMMAND_SAY = "watermossmc.command.say";
	public const COMMAND_SEED = "watermossmc.command.seed";
	public const COMMAND_SETWORLDSPAWN = "watermossmc.command.setworldspawn";
	public const COMMAND_SPAWNPOINT_OTHER = "watermossmc.command.spawnpoint.other";
	public const COMMAND_SPAWNPOINT_SELF = "watermossmc.command.spawnpoint.self";
	public const COMMAND_STATUS = "watermossmc.command.status";
	public const COMMAND_STOP = "watermossmc.command.stop";
	public const COMMAND_TELEPORT_OTHER = "watermossmc.command.teleport.other";
	public const COMMAND_TELEPORT_SELF = "watermossmc.command.teleport.self";
	public const COMMAND_TELL = "watermossmc.command.tell";
	public const COMMAND_TIME_ADD = "watermossmc.command.time.add";
	public const COMMAND_TIME_QUERY = "watermossmc.command.time.query";
	public const COMMAND_TIME_SET = "watermossmc.command.time.set";
	public const COMMAND_TIME_START = "watermossmc.command.time.start";
	public const COMMAND_TIME_STOP = "watermossmc.command.time.stop";
	public const COMMAND_TIMINGS = "watermossmc.command.timings";
	public const COMMAND_TITLE_OTHER = "watermossmc.command.title.other";
	public const COMMAND_TITLE_SELF = "watermossmc.command.title.self";
	public const COMMAND_TRANSFERSERVER = "watermossmc.command.transferserver";
	public const COMMAND_UNBAN_IP = "watermossmc.command.unban.ip";
	public const COMMAND_UNBAN_PLAYER = "watermossmc.command.unban.player";
	public const COMMAND_VERSION = "watermossmc.command.version";
	public const COMMAND_WHITELIST_ADD = "watermossmc.command.whitelist.add";
	public const COMMAND_WHITELIST_DISABLE = "watermossmc.command.whitelist.disable";
	public const COMMAND_WHITELIST_ENABLE = "watermossmc.command.whitelist.enable";
	public const COMMAND_WHITELIST_LIST = "watermossmc.command.whitelist.list";
	public const COMMAND_WHITELIST_RELOAD = "watermossmc.command.whitelist.reload";
	public const COMMAND_WHITELIST_REMOVE = "watermossmc.command.whitelist.remove";
	public const COMMAND_XP_OTHER = "watermossmc.command.xp.other";
	public const COMMAND_XP_SELF = "watermossmc.command.xp.self";
	public const GROUP_CONSOLE = "watermossmc.group.console";
	public const GROUP_OPERATOR = "watermossmc.group.operator";
	public const GROUP_USER = "watermossmc.group.user";
}
