<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\types;

final class DisconnectReason
{
    public const UNKNOWN = 0;
    public const CANT_CONNECT_NO_INTERNET = 1;
    public const NO_PERMISSIONS = 2;
    public const UNRECOVERABLE_ERROR = 3;
    public const THIRD_PARTY_BLOCKED = 4;
    public const THIRD_PARTY_NO_INTERNET = 5;
    public const THIRD_PARTY_BAD_IP = 6;
    public const THIRD_PARTY_NO_SERVER_OR_SERVER_LOCKED = 7;
    public const VERSION_MISMATCH = 8;
    public const SKIN_ISSUE = 9;
    public const INVITE_SESSION_NOT_FOUND = 10;
    public const EDU_LEVEL_SETTINGS_MISSING = 11;
    public const LOCAL_SERVER_NOT_FOUND = 12;
    public const LEGACY_DISCONNECT = 13;
    public const INTERNAL_USER_LEAVE_GAME_ATTEMPTED = 14;
    public const PLATFORM_LOCKED_SKINS_ERROR = 15;
    public const REALMS_WORLD_UNASSIGNED = 16;
    public const REALMS_SERVER_CANT_CONNECT = 17;
    public const REALMS_SERVER_HIDDEN = 18;
    public const REALMS_SERVER_DISABLED_BETA = 19;
    public const REALMS_SERVER_DISABLED = 20;
    public const CROSS_PLATFORM_DISABLED = 21;
    public const TESTONLY_CANT_CONNECT = 22;
    public const SESSION_NOT_FOUND = 23;
    public const DEPRECATED_CLIENT_SETTINGS_INCOMPATIBLE_WITH_SERVER = 24;
    public const SERVER_FULL = 25;
    public const INVALID_PLATFORM_SKIN = 26;
    public const EDITION_VERSION_MISMATCH = 27;
    public const EDITION_MISMATCH = 28;
    public const LEVEL_NEWER_THAN_EXE_VERSION = 29;
    public const INTERNAL_NO_FAIL_OCCURRED = 30;
    public const BANNED_SKIN = 31;
    public const TIMEOUT = 32;
    public const SERVER_NOT_FOUND = 33;
    public const OUTDATED_SERVER = 34;
    public const OUTDATED_CLIENT = 35;
    public const DEPRECATED_NO_PREMIUM_PLATFORM = 36;
    public const MULTIPLAYER_DISABLED = 37;
    public const NO_WIFI = 38;
    public const DEPRECATED_WORLD_CORRUPTION = 39;
    public const NO_REASON = 40;
    public const DISCONNECTED = 41;
    public const INVALID_PLAYER = 42;
    public const LOGGED_IN_OTHER_LOCATION = 43;
    public const SERVER_ID_CONFLICT = 44;
    public const NOT_ALLOWED = 45;
    public const NOT_AUTHENTICATED = 46;
    public const INVALID_TENANT = 47;
    public const UNKNOWN_PACKET = 48;
    public const UNEXPECTED_PACKET = 49;
    public const INVALID_COMMAND_REQUEST_PACKET = 50;
    public const HOST_SUSPENDED = 51;
    public const LOGIN_PACKET_NO_REQUEST = 52;
    public const LOGIN_PACKET_NO_CERT = 53;
    public const MISSING_CLIENT = 54;
    public const KICKED = 55;
    public const KICKED_FOR_EXPLOIT = 56;
    public const KICKED_FOR_IDLE = 57;
    public const RESOURCE_PACK_PROBLEM = 58;
    public const INCOMPATIBLE_PACK = 59;
    public const OUT_OF_STORAGE = 60;
    public const INVALID_LEVEL = 61;
    public const DEPRECATED_DISCONNECT_PACKET = 62;
    public const BLOCK_MISMATCH = 63;
    public const INVALID_HEIGHTS = 64;
    public const INVALID_WIDTHS = 65;
    public const DEPRECATED_CONNECTION_LOST = 66;
    public const DEPRECATED_ZOMBIE_CONNECTION = 67;
    public const SHUTDOWN = 68;
    public const DEPRECATED_REASON_NOT_SET = 69;
    public const LOADING_STATE_TIMEOUT = 70;
    public const RESOURCE_PACK_LOADING_FAILED = 71;
    public const SEARCHING_FOR_SESSION_LOADING_SCREEN_FAILED = 72;
    public const NETHER_NET_PROTOCOL_VERSION = 73;
    public const SUBSYSTEM_STATUS_ERROR = 74;
    public const EMPTY_AUTH_FROM_DISCOVERY = 75;
    public const EMPTY_URL_FROM_DISCOVERY = 76;
    public const EXPIRED_AUTH_FROM_DISCOVERY = 77;
    public const UNKNOWN_SIGNAL_SERVICE_SIGN_IN_FAILURE = 78;
    public const XBL_JOIN_LOBBY_FAILURE = 79;
    public const UNSPECIFIED_CLIENT_INSTANCE_DISCONNECTION = 80;
    public const NETHER_NET_SESSION_NOT_FOUND = 81;
    public const NETHER_NET_CREATE_PEER_CONNECTION = 82;
    public const NETHER_NET_ICE = 83;
    public const NETHER_NET_CONNECT_REQUEST = 84;
    public const NETHER_NET_CONNECT_RESPONSE = 85;
    public const NETHER_NET_NEGOTIATION_TIMEOUT = 86;
    public const NETHER_NET_INACTIVITY_TIMEOUT = 87;
    public const STALE_CONNECTION_BEING_REPLACED = 88;
    public const DEPRECATED_REALMS_SESSION_NOT_FOUND = 89;
    public const BAD_PACKET = 90;
    public const NETHER_NET_FAILED_TO_CREATE_OFFER = 91;
    public const NETHER_NET_FAILED_TO_CREATE_ANSWER = 92;
    public const NETHER_NET_FAILED_TO_SET_LOCAL_DESCRIPTION = 93;
    public const NETHER_NET_FAILED_TO_SET_REMOTE_DESCRIPTION = 94;
    public const NETHER_NET_NEGOTIATION_TIMEOUT_WAITING_FOR_RESPONSE = 95;
    public const NETHER_NET_NEGOTIATION_TIMEOUT_WAITING_FOR_ACCEPT = 96;
    public const NETHER_NET_INCOMING_CONNECTION_IGNORED = 97;
    public const NETHER_NET_SIGNALING_PARSING_FAILURE = 98;
    public const NETHER_NET_SIGNALING_UNKNOWN_ERROR = 99;
    public const NETHER_NET_SIGNALING_UNICAST_DELIVERY_FAILED = 100;
    public const NETHER_NET_SIGNALING_BROADCAST_DELIVERY_FAILED = 101;
    public const NETHER_NET_SIGNALING_GENERIC_DELIVERY_FAILED = 102;
    public const EDITOR_MISMATCH_EDITOR_WORLD = 103;
    public const EDITOR_MISMATCH_VANILLA_WORLD = 104;
    public const WORLD_TRANSFER_NOT_PRIMARY_CLIENT = 105;
    public const INTERNAL_REQUEST_SERVER_SHUTDOWN = 106;
    public const CLIENT_GAME_SETUP_CANCELLED = 107;
    public const CLIENT_GAME_SETUP_FAILED = 108;
    public const DEPRECATED_NO_VENUE = 109;
    public const NETHER_NET_SIGNALING_SIGNIN_FAILED = 110;
    public const SESSION_ACCESS_DENIED = 111;
    public const SERVICE_SIGNIN_ISSUE = 112;
    public const NETHER_NET_NO_SIGNALING_CHANNEL = 113;
    public const NETHER_NET_NOT_LOGGED_IN = 114;
    public const NETHER_NET_CLIENT_SIGNALING_ERROR = 115;
    public const SUB_CLIENT_LOGIN_DISABLED = 116;
    public const DEEP_LINK_TRYING_TO_OPEN_DEMO_WORLD_WHILE_SIGNED_IN = 117;
    public const ASYNC_JOIN_TASK_DENIED = 118;
    public const REALMS_TIMELINE_REQUIRED = 119;
    public const GUEST_WITHOUT_HOST = 120;
    public const FAILED_TO_JOIN_EXPERIENCE = 121;
    public const NETHER_NET_DATA_CHANNEL_CLOSED = 122;
    public const DISCOVERY_ENVIRONMENT_MISMATCH = 123;
    public const HOST_WITHOUT_KEYS = 124;
    public const HOST_SIGNED_OUT = 125;
    public const SCRIPT_WATCHDOG_EXCEPTION = 126;
    public const SCRIPT_MEMORY_LIMIT_EXCEEDED = 127;
    public const STORAGE_LOW_DURING_GAMEPLAY = 128;
    public const STORAGE_FULL_DURING_GAMEPLAY = 129;
    public const LEVEL_STORAGE_CORRUPTION = 130;
    public const EDITION_MISMATCH_VANILLA_TO_EDU = 131;
    public const EDITION_MISMATCH_EDU_TO_VANILLA = 132;
    public const EDITION_MISMATCH_EDITOR_TO_VANILLA = 133;
    public const EDITION_MISMATCH_VANILLA_TO_EDITOR = 134;
    public const DENY_LISTED = 135;
    public const NONCE_MISSING = 136;
    public const NONCE_NOT_FOUND = 137;
    public const NONCE_EXPIRED = 138;
    public const HOST_DISCONNECTED = 140;
    public const NONCE_NOT_VALID = 139;
    public const EDITOR_JOIN_INTENT_POLICY_FAILURE = 141;
    public const NETHER_NET_IDENTITY_NOT_ALLOWED = 142;
    public const INVALID_NAME = 143;
    public const EXPIRED_TOKEN = 144;
    public const HOST_ACCEPTS_NO_TYPE_OF_AUTH = 145;
    public const NOT_AUTHENTICATED_FAST_FAIL = 146;
    public const EDITOR_NOT_ALLOWED = 147;
    public const MISSING_STRUCTURE_DATA = 148;
    public const UNSUPPORTED_TRANSPORT = 149;
}
