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

namespace watermossmc\updater;

/**
 * Model class for JsonMapper to represent the information returned from the updater API.
 * @link https://update.pmmp.io/api
 */
final class UpdateInfo
{
	/** @required */
	public string $php_version;
	/** @required */
	public string $base_version;
	/** @required */
	public bool $is_dev;
	/** @required */
	public string $channel;
	/** @required */
	public string $git_commit;
	/** @required */
	public string $mcpe_version;
	/** @required */
	public int $build;
	/** @required */
	public int $date;
	/** @required */
	public string $details_url;
	/** @required */
	public string $download_url;
	/** @required */
	public string $source_url;
}
