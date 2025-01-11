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

namespace watermossmc;

/**
 * @internal
 * Constants for all properties available in watermossmc.yml.
 * This is generated by build/generate-watermossmc-yml-property-consts.php.
 * Do not edit this file manually.
 */
final class YmlServerProperties
{
	private function __construct()
	{
		//NOOP
	}

	public const ALIASES = 'aliases';
	public const ANONYMOUS_STATISTICS = 'anonymous-statistics';
	public const ANONYMOUS_STATISTICS_ENABLED = 'anonymous-statistics.enabled';
	public const ANONYMOUS_STATISTICS_HOST = 'anonymous-statistics.host';
	public const AUTO_REPORT = 'auto-report';
	public const AUTO_REPORT_ENABLED = 'auto-report.enabled';
	public const AUTO_REPORT_HOST = 'auto-report.host';
	public const AUTO_REPORT_SEND_CODE = 'auto-report.send-code';
	public const AUTO_REPORT_SEND_PHPINFO = 'auto-report.send-phpinfo';
	public const AUTO_REPORT_SEND_SETTINGS = 'auto-report.send-settings';
	public const AUTO_REPORT_USE_HTTPS = 'auto-report.use-https';
	public const AUTO_UPDATER = 'auto-updater';
	public const AUTO_UPDATER_ENABLED = 'auto-updater.enabled';
	public const AUTO_UPDATER_HOST = 'auto-updater.host';
	public const AUTO_UPDATER_ON_UPDATE = 'auto-updater.on-update';
	public const AUTO_UPDATER_ON_UPDATE_WARN_CONSOLE = 'auto-updater.on-update.warn-console';
	public const AUTO_UPDATER_PREFERRED_CHANNEL = 'auto-updater.preferred-channel';
	public const AUTO_UPDATER_SUGGEST_CHANNELS = 'auto-updater.suggest-channels';
	public const CHUNK_GENERATION = 'chunk-generation';
	public const CHUNK_GENERATION_POPULATION_QUEUE_SIZE = 'chunk-generation.population-queue-size';
	public const CHUNK_SENDING = 'chunk-sending';
	public const CHUNK_SENDING_PER_TICK = 'chunk-sending.per-tick';
	public const CHUNK_SENDING_SPAWN_RADIUS = 'chunk-sending.spawn-radius';
	public const CHUNK_TICKING = 'chunk-ticking';
	public const CHUNK_TICKING_BLOCKS_PER_SUBCHUNK_PER_TICK = 'chunk-ticking.blocks-per-subchunk-per-tick';
	public const CHUNK_TICKING_DISABLE_BLOCK_TICKING = 'chunk-ticking.disable-block-ticking';
	public const CHUNK_TICKING_TICK_RADIUS = 'chunk-ticking.tick-radius';
	public const CONSOLE = 'console';
	public const CONSOLE_ENABLE_INPUT = 'console.enable-input';
	public const CONSOLE_TITLE_TICK = 'console.title-tick';
	public const DEBUG = 'debug';
	public const DEBUG_LEVEL = 'debug.level';
	public const LEVEL_SETTINGS = 'level-settings';
	public const LEVEL_SETTINGS_DEFAULT_FORMAT = 'level-settings.default-format';
	public const MEMORY = 'memory';
	public const MEMORY_ASYNC_WORKER_HARD_LIMIT = 'memory.async-worker-hard-limit';
	public const MEMORY_CHECK_RATE = 'memory.check-rate';
	public const MEMORY_CONTINUOUS_TRIGGER = 'memory.continuous-trigger';
	public const MEMORY_CONTINUOUS_TRIGGER_RATE = 'memory.continuous-trigger-rate';
	public const MEMORY_GARBAGE_COLLECTION = 'memory.garbage-collection';
	public const MEMORY_GARBAGE_COLLECTION_COLLECT_ASYNC_WORKER = 'memory.garbage-collection.collect-async-worker';
	public const MEMORY_GARBAGE_COLLECTION_LOW_MEMORY_TRIGGER = 'memory.garbage-collection.low-memory-trigger';
	public const MEMORY_GARBAGE_COLLECTION_PERIOD = 'memory.garbage-collection.period';
	public const MEMORY_GLOBAL_LIMIT = 'memory.global-limit';
	public const MEMORY_MAIN_HARD_LIMIT = 'memory.main-hard-limit';
	public const MEMORY_MAIN_LIMIT = 'memory.main-limit';
	public const MEMORY_MAX_CHUNKS = 'memory.max-chunks';
	public const MEMORY_MAX_CHUNKS_CHUNK_RADIUS = 'memory.max-chunks.chunk-radius';
	public const MEMORY_MAX_CHUNKS_TRIGGER_CHUNK_COLLECT = 'memory.max-chunks.trigger-chunk-collect';
	public const MEMORY_MEMORY_DUMP = 'memory.memory-dump';
	public const MEMORY_MEMORY_DUMP_DUMP_ASYNC_WORKER = 'memory.memory-dump.dump-async-worker';
	public const MEMORY_WORLD_CACHES = 'memory.world-caches';
	public const MEMORY_WORLD_CACHES_DISABLE_CHUNK_CACHE = 'memory.world-caches.disable-chunk-cache';
	public const MEMORY_WORLD_CACHES_LOW_MEMORY_TRIGGER = 'memory.world-caches.low-memory-trigger';
	public const NETWORK = 'network';
	public const NETWORK_ASYNC_COMPRESSION = 'network.async-compression';
	public const NETWORK_ASYNC_COMPRESSION_THRESHOLD = 'network.async-compression-threshold';
	public const NETWORK_BATCH_THRESHOLD = 'network.batch-threshold';
	public const NETWORK_COMPRESSION_LEVEL = 'network.compression-level';
	public const NETWORK_ENABLE_ENCRYPTION = 'network.enable-encryption';
	public const NETWORK_MAX_MTU_SIZE = 'network.max-mtu-size';
	public const NETWORK_UPNP_FORWARDING = 'network.upnp-forwarding';
	public const PLAYER = 'player';
	public const PLAYER_SAVE_PLAYER_DATA = 'player.save-player-data';
	public const PLAYER_VERIFY_XUID = 'player.verify-xuid';
	public const PLUGINS = 'plugins';
	public const PLUGINS_LEGACY_DATA_DIR = 'plugins.legacy-data-dir';
	public const SETTINGS = 'settings';
	public const SETTINGS_ASYNC_WORKERS = 'settings.async-workers';
	public const SETTINGS_ENABLE_DEV_BUILDS = 'settings.enable-dev-builds';
	public const SETTINGS_ENABLE_PROFILING = 'settings.enable-profiling';
	public const SETTINGS_FORCE_LANGUAGE = 'settings.force-language';
	public const SETTINGS_PROFILE_REPORT_TRIGGER = 'settings.profile-report-trigger';
	public const SETTINGS_QUERY_PLUGINS = 'settings.query-plugins';
	public const SETTINGS_SHUTDOWN_MESSAGE = 'settings.shutdown-message';
	public const TICKS_PER = 'ticks-per';
	public const TICKS_PER_AUTOSAVE = 'ticks-per.autosave';
	public const TIMINGS = 'timings';
	public const TIMINGS_HOST = 'timings.host';
	public const WORLDS = 'worlds';
}
