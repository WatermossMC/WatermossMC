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

use watermossmc\scheduler\AsyncTask;
use watermossmc\utils\Internet;

use function is_array;
use function is_string;
use function json_decode;

class UpdateCheckTask extends AsyncTask
{
	private const TLS_KEY_UPDATER = "updater";

	private string $error = "Unknown error";

	public function __construct(
		UpdateChecker $updater,
		private string $endpoint,
		private string $channel
	) {
		$this->storeLocal(self::TLS_KEY_UPDATER, $updater);
	}

	public function onRun() : void
	{
		$error = "";
		$response = Internet::getURL($this->endpoint . "?channel=" . $this->channel, 4, [], $error);
		$this->error = $error;

		if ($response !== null) {
			$response = json_decode($response->getBody(), true);
			if (is_array($response)) {
				if (isset($response["error"]) && is_string($response["error"])) {
					$this->error = $response["error"];
				} else {
					$mapper = new \JsonMapper();
					$mapper->bExceptionOnMissingData = true;
					$mapper->bStrictObjectTypeChecking = true;
					$mapper->bEnforceMapType = false;
					try {
						/** @var UpdateInfo $responseObj */
						$responseObj = $mapper->map($response, new UpdateInfo());
						$this->setResult($responseObj);
					} catch (\JsonMapper_Exception $e) {
						$this->error = "Invalid JSON response data: " . $e->getMessage();
					}
				}
			} else {
				$this->error = "Invalid response data";
			}
		}
	}

	public function onCompletion() : void
	{
		/** @var UpdateChecker $updater */
		$updater = $this->fetchLocal(self::TLS_KEY_UPDATER);
		if ($this->hasResult()) {
			/** @var UpdateInfo $response */
			$response = $this->getResult();
			$updater->checkUpdateCallback($response);
		} else {
			$updater->checkUpdateError($this->error);
		}
	}
}
