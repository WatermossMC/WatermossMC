<?php

/*
 * This file is part of RakLib.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/RakLib>
 *
 * RakLib is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * RakLib is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace watermossmc
etworkaklibserver;

use watermossmc
etworkaklibgeneric\Session;
use watermossmc
etworkaklibprotocol\ConnectionRequest;
use watermossmc
etworkaklibprotocol\ConnectionRequestAccepted;
use watermossmc
etworkaklibprotocol\MessageIdentifiers;
use watermossmc
etworkaklibprotocol\NewIncomingConnection;
use watermossmc
etworkaklibprotocol\Packet;
use watermossmc
etworkaklibprotocol\PacketReliability;
use watermossmc
etworkaklibprotocol\PacketSerializer;
use watermossmc
etworkaklibutils\InternetAddress;
use function ord;

class ServerSession extends Session{
	public const DEFAULT_MAX_SPLIT_PART_COUNT = 128;
	public const DEFAULT_MAX_CONCURRENT_SPLIT_COUNT = 4;

	private Server $server;
	private int $internalId;

	public function __construct(
		Server $server,
		\Logger $logger,
		InternetAddress $address,
		int $clientId,
		int $mtuSize,
		int $internalId,
		int $recvMaxSplitParts = self::DEFAULT_MAX_SPLIT_PART_COUNT,
		int $recvMaxConcurrentSplits = self::DEFAULT_MAX_CONCURRENT_SPLIT_COUNT
	){
		$this->server = $server;
		$this->internalId = $internalId;
		parent::__construct($logger, $address, $clientId, $mtuSize, $recvMaxSplitParts, $recvMaxConcurrentSplits);
	}

	/**
	 * Returns an ID used to identify this session across threads.
	 */
	public function getInternalId() : int{
		return $this->internalId;
	}

	final protected function sendPacket(Packet $packet) : void{
		$this->server->sendPacket($packet, $this->address);
	}

	protected function onPacketAck(int $identifierACK) : void{
		$this->server->getEventListener()->onPacketAck($this->internalId, $identifierACK);
	}

	protected function onDisconnect(int $reason) : void{
		$this->server->getEventListener()->onClientDisconnect($this->internalId, $reason);
	}

	final protected function handleRakNetConnectionPacket(string $packet) : void{
		$id = ord($packet[0]);
		if($id === MessageIdentifiers::ID_CONNECTION_REQUEST){
			$dataPacket = new ConnectionRequest();
			$dataPacket->decode(new PacketSerializer($packet));
			$this->queueConnectedPacket(ConnectionRequestAccepted::create(
				$this->address,
				[],
				$dataPacket->sendPingTime,
				$this->getRakNetTimeMS()
			), PacketReliability::UNRELIABLE, 0, true);
		}elseif($id === MessageIdentifiers::ID_NEW_INCOMING_CONNECTION){
			$dataPacket = new NewIncomingConnection();
			$dataPacket->decode(new PacketSerializer($packet));

			if($dataPacket->address->getPort() === $this->server->getPort() or !$this->server->portChecking){
				$this->state = self::STATE_CONNECTED; //FINALLY!
				$this->server->openSession($this);

				//$this->handlePong($dataPacket->sendPingTime, $dataPacket->sendPongTime); //can't use this due to system-address count issues in MCPE >.<
				$this->sendPing();
			}
		}
	}

	protected function onPacketReceive(string $packet) : void{
		$this->server->getEventListener()->onPacketReceive($this->internalId, $packet);
	}

	protected function onPingMeasure(int $pingMS) : void{
		$this->server->getEventListener()->onPingMeasure($this->internalId, $pingMS);
	}
}
