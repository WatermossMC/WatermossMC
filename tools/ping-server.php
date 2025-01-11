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

namespace watermossmc\tools\ping_server;

use watermossmc\network\raklib\protocol\MessageIdentifiers;
use watermossmc\network\raklib\protocol\PacketSerializer;
use watermossmc\network\raklib\protocol\UnconnectedPing;
use watermossmc\network\raklib\protocol\UnconnectedPong;
use watermossmc\utils\Utils;

use function bin2hex;
use function count;
use function fgets;
use function gethostbynamel;
use function hrtime;
use function intdiv;
use function mt_rand;
use function ord;
use function sleep;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_getsockname;
use function socket_last_error;
use function socket_recvfrom;
use function socket_select;
use function socket_sendto;
use function socket_strerror;
use function strlen;
use function time;
use function trim;

use const AF_INET;
use const MSG_DONTROUTE;
use const PHP_BINARY;
use const PHP_INT_MAX;
use const SOCK_DGRAM;
use const SOL_UDP;
use const STDIN;

require_once 'vendor/autoload.php';

function hrtime_ms() : int
{
	return intdiv(hrtime(true), 1_000_000);
}

function read_stdin(string $prompt) : string
{
	echo $prompt . ": ";
	$input = fgets(STDIN);
	if ($input === false) {
		exit(1); //this probably means the user pressed ctrl+c
	}
	return trim($input);
}

function ping_server(\Socket $socket, string $serverIp, int $serverPort, int $timeoutSeconds, int $rakNetClientId) : bool
{
	$ping = new UnconnectedPing();
	$ping->sendPingTime = hrtime_ms();
	$ping->clientId = $rakNetClientId;
	$serializer = new PacketSerializer();
	$ping->encode($serializer);
	if (@socket_sendto($socket, $serializer->getBuffer(), strlen($serializer->getBuffer()), MSG_DONTROUTE, $serverIp, $serverPort) === false) {
		\GlobalLogger::get()->error("Failed to send ping: " . socket_strerror(socket_last_error($socket)));
		return false;
	}
	\GlobalLogger::get()->info("Ping sent to $serverIp on port $serverPort, waiting for response (press CTRL+C to abort)");

	$r = [$socket];
	$w = $e = null;
	if (socket_select($r, $w, $e, $timeoutSeconds) === 1) {
		$response = @socket_recvfrom($socket, $recvBuffer, 65535, 0, $recvAddr, $recvPort);
		if ($response === false) {
			\GlobalLogger::get()->error("Error reading from socket: " . socket_strerror(socket_last_error($socket)));
			return false;
		}
		if ($recvAddr === $serverIp && $recvPort === $serverPort && $recvBuffer !== "" && ord($recvBuffer[0]) === MessageIdentifiers::ID_UNCONNECTED_PONG) {
			$pong = new UnconnectedPong();
			$pong->decode(new PacketSerializer($recvBuffer));
			\GlobalLogger::get()->info("--- Response received ---");
			\GlobalLogger::get()->info("Payload: $pong->serverName");
			\GlobalLogger::get()->info("Response time: " . (hrtime_ms() - $pong->sendPingTime) . " ms");
			return true;
		} else {
			\GlobalLogger::get()->debug("Garbage packet from $recvAddr $recvPort: " . bin2hex($recvBuffer));
		}
	}

	\GlobalLogger::get()->info("No ping response after $timeoutSeconds seconds");
	return false;
}

if (count($argv) > 3) {
	echo "Usage: " . PHP_BINARY . " " . __FILE__ . " [server IP] [server port]\n";
	exit(1);
}

if (count($argv) > 1) {
	$hostName = $argv[1];
} else {
	do {
		$hostName = read_stdin("Server address");
	} while ($hostName === "");
}

$serverIps = gethostbynamel($hostName);
if ($serverIps === false) {
	\GlobalLogger::get()->critical("Unable to resolve hostname $hostName to an IP address");
	exit(1);
}
if (count($serverIps) > 1) {
	\GlobalLogger::get()->warning("Multiple IP addresses found for hostname $hostName, using the first one: " . $serverIps[0]);
}
$server = $serverIps[0];
\GlobalLogger::get()->info("Resolved hostname to $server");

if (count($argv) > 2) {
	$port = (int) $argv[2];
} elseif (count($argv) > 1) {
	$port = 19132;
} else {
	$portRaw = read_stdin("Server port (empty for 19132)");
	$port = $portRaw === "" ? 19132 : (int) $portRaw;
}

$sock = Utils::assumeNotFalse(socket_create(AF_INET, SOCK_DGRAM, SOL_UDP));

socket_bind($sock, "0.0.0.0");
socket_getsockname($sock, $bindAddr, $bindPort);
\GlobalLogger::get()->info("Bound to $bindAddr on port $bindPort");

$start = time();
while (time() < $start + 60_000 && !ping_server($sock, $server, $port, 5, mt_rand(0, PHP_INT_MAX))) {
	sleep(1);
}

socket_close($sock);
