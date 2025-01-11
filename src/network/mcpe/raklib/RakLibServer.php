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

namespace watermossmc\network\mcpe\raklib;

use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use watermossmc\network\raklib\generic\SocketException;
use watermossmc\network\raklib\server\ipc\RakLibToUserThreadMessageSender;
use watermossmc\network\raklib\server\ipc\UserToRakLibThreadMessageReceiver;
use watermossmc\network\raklib\server\Server;
use watermossmc\network\raklib\server\ServerSocket;
use watermossmc\network\raklib\server\SimpleProtocolAcceptor;
use watermossmc\network\raklib\utils\ExceptionTraceCleaner;
use watermossmc\network\raklib\utils\InternetAddress;
use watermossmc\snooze\SleeperHandlerEntry;
use watermossmc\thread\log\ThreadSafeLogger;
use watermossmc\thread\NonThreadSafeValue;
use watermossmc\thread\Thread;
use watermossmc\thread\ThreadCrashException;

use function gc_enable;
use function ini_set;

class RakLibServer extends Thread
{
	protected bool $ready = false;
	protected string $mainPath;
	/** @phpstan-var NonThreadSafeValue<InternetAddress> */
	protected NonThreadSafeValue $address;

	/**
	 * @phpstan-param ThreadSafeArray<int, string> $mainToThreadBuffer
	 * @phpstan-param ThreadSafeArray<int, string> $threadToMainBuffer
	 */
	public function __construct(
		protected ThreadSafeLogger $logger,
		protected ThreadSafeArray $mainToThreadBuffer,
		protected ThreadSafeArray $threadToMainBuffer,
		InternetAddress $address,
		protected int $serverId,
		protected int $maxMtuSize,
		protected int $protocolVersion,
		protected SleeperHandlerEntry $sleeperEntry
	) {
		$this->mainPath = \watermossmc\PATH;
		$this->address = new NonThreadSafeValue($address);
	}

	public function startAndWait(int $options = NativeThread::INHERIT_NONE) : void
	{
		$this->start($options);
		$this->synchronized(function () : void {
			while (!$this->ready && $this->getCrashInfo() === null) {
				$this->wait();
			}
			$crashInfo = $this->getCrashInfo();
			if ($crashInfo !== null) {
				if ($crashInfo->getType() === SocketException::class) {
					throw new SocketException($crashInfo->getMessage());
				}
				throw new ThreadCrashException("RakLib failed to start", $crashInfo);
			}
		});
	}

	protected function onRun() : void
	{
		gc_enable();
		ini_set("display_errors", '1');
		ini_set("display_startup_errors", '1');
		\GlobalLogger::set($this->logger);

		$socket = new ServerSocket($this->address->deserialize());
		$manager = new Server(
			$this->serverId,
			$this->logger,
			$socket,
			$this->maxMtuSize,
			new SimpleProtocolAcceptor($this->protocolVersion),
			new UserToRakLibThreadMessageReceiver(new PthreadsChannelReader($this->mainToThreadBuffer)),
			new RakLibToUserThreadMessageSender(new SnoozeAwarePthreadsChannelWriter($this->threadToMainBuffer, $this->sleeperEntry->createNotifier())),
			new ExceptionTraceCleaner($this->mainPath),
			recvMaxSplitParts: 512
		);
		$this->synchronized(function () : void {
			$this->ready = true;
			$this->notify();
		});
		while (!$this->isKilled) {
			$manager->tickProcessor();
		}
		$manager->waitShutdown();
	}

	public function getThreadName() : string
	{
		return "RakLib";
	}
}
