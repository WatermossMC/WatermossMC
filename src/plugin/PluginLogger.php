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

use function spl_object_id;

/**
 * @phpstan-import-type LoggerAttachment from \AttachableLogger
 */
class PluginLogger extends \PrefixedLogger implements \AttachableLogger
{
	/**
	 * @var \Closure[]
	 * @phpstan-var LoggerAttachment[]
	 */
	private array $attachments = [];

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 */
	public function addAttachment(\Closure $attachment)
	{
		$this->attachments[spl_object_id($attachment)] = $attachment;
	}

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 */
	public function removeAttachment(\Closure $attachment)
	{
		unset($this->attachments[spl_object_id($attachment)]);
	}

	public function removeAttachments()
	{
		$this->attachments = [];
	}

	public function getAttachments()
	{
		return $this->attachments;
	}

	public function log($level, $message)
	{
		parent::log($level, $message);
		foreach ($this->attachments as $attachment) {
			$attachment($level, $message);
		}
	}
}
