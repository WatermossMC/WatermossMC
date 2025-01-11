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

namespace watermossmc\network\upnp;

use watermossmc\network\NetworkInterface;
use watermossmc\utils\Internet;
use watermossmc\utils\InternetException;

final class UPnPNetworkInterface implements NetworkInterface
{
	private ?string $serviceURL = null;

	public function __construct(
		private \Logger $logger,
		private string $ip,
		private int $port
	) {
		if (!Internet::$online) {
			throw new \RuntimeException("Server is offline");
		}

		$this->logger = new \PrefixedLogger($logger, "UPnP Port Forwarder");
	}

	public function start() : void
	{
		$this->logger->info("Attempting to portforward...");

		try {
			$this->serviceURL = UPnP::getServiceUrl();
			UPnP::portForward($this->serviceURL, Internet::getInternalIP(), $this->port, $this->port);
			$this->logger->info("Forwarded $this->ip:$this->port to external port $this->port");
		} catch (UPnPException | InternetException $e) {
			$this->logger->error("UPnP portforward failed: " . $e->getMessage());
		}
	}

	public function setName(string $name) : void
	{

	}

	public function tick() : void
	{

	}

	public function shutdown() : void
	{
		if ($this->serviceURL === null) {
			return;
		}

		UPnP::removePortForward($this->serviceURL, $this->port);
	}
}
