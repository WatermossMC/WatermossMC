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

namespace watermossmc\network\mcpe;

use watermossmc\network\mcpe\compression\CompressBatchPromise;
use watermossmc\network\mcpe\compression\Compressor;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\LevelChunkPacket;
use watermossmc\network\mcpe\protocol\serializer\PacketBatch;
use watermossmc\network\mcpe\protocol\types\ChunkPosition;
use watermossmc\network\mcpe\protocol\types\DimensionIds;
use watermossmc\network\mcpe\serializer\ChunkSerializer;
use watermossmc\scheduler\AsyncTask;
use watermossmc\thread\NonThreadSafeValue;
use watermossmc\utils\BinaryStream;
use watermossmc\world\format\Chunk;
use watermossmc\world\format\io\FastChunkSerializer;

use function chr;

class ChunkRequestTask extends AsyncTask
{
	private const TLS_KEY_PROMISE = "promise";

	protected string $chunk;
	protected int $chunkX;
	protected int $chunkZ;
	/** @phpstan-var DimensionIds::* */
	private int $dimensionId;
	/** @phpstan-var NonThreadSafeValue<Compressor> */
	protected NonThreadSafeValue $compressor;
	private string $tiles;

	/**
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public function __construct(int $chunkX, int $chunkZ, int $dimensionId, Chunk $chunk, CompressBatchPromise $promise, Compressor $compressor)
	{
		$this->compressor = new NonThreadSafeValue($compressor);

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$this->dimensionId = $dimensionId;
		$this->tiles = ChunkSerializer::serializeTiles($chunk);

		$this->storeLocal(self::TLS_KEY_PROMISE, $promise);
	}

	public function onRun() : void
	{
		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
		$dimensionId = $this->dimensionId;

		$subCount = ChunkSerializer::getSubChunkCount($chunk, $dimensionId);
		$converter = TypeConverter::getInstance();
		$payload = ChunkSerializer::serializeFullChunk($chunk, $dimensionId, $converter->getBlockTranslator(), $this->tiles);

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, [LevelChunkPacket::create(new ChunkPosition($this->chunkX, $this->chunkZ), $dimensionId, $subCount, false, null, $payload)]);

		$compressor = $this->compressor->deserialize();
		$this->setResult(chr($compressor->getNetworkId()) . $compressor->compress($stream->getBuffer()));
	}

	public function onCompletion() : void
	{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal(self::TLS_KEY_PROMISE);
		$promise->resolve($this->getResult());
	}
}
