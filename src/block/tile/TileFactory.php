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

namespace watermossmc\block\tile;

use watermossmc\data\SavedDataLoadingException;
use watermossmc\math\Vector3;
use watermossmc\nbt\NbtException;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;
use watermossmc\world\World;

use function assert;
use function in_array;
use function is_a;
use function reset;

final class TileFactory
{
	use SingletonTrait;

	/**
	 * @var string[] classes that extend Tile
	 * @phpstan-var array<string, class-string<Tile>>
	 */
	private array $knownTiles = [];
	/**
	 * @var string[]
	 * @phpstan-var array<class-string<Tile>, string>
	 */
	private array $saveNames = [];

	public function __construct()
	{
		$this->register(Barrel::class, ["Barrel", "minecraft:barrel"]);
		$this->register(Banner::class, ["Banner", "minecraft:banner"]);
		$this->register(Beacon::class, ["Beacon", "minecraft:beacon"]);
		$this->register(Bed::class, ["Bed", "minecraft:bed"]);
		$this->register(Bell::class, ["Bell", "minecraft:bell"]);
		$this->register(BlastFurnace::class, ["BlastFurnace", "minecraft:blast_furnace"]);
		$this->register(BrewingStand::class, ["BrewingStand", "minecraft:brewing_stand"]);
		$this->register(Campfire::class, ["Campfire", "minecraft:campfire"]);
		$this->register(Cauldron::class, ["Cauldron", "minecraft:cauldron"]);
		$this->register(Chest::class, ["Chest", "minecraft:chest"]);
		$this->register(ChiseledBookshelf::class, ["ChiseledBookshelf", "minecraft:chiseled_bookshelf"]);
		$this->register(Comparator::class, ["Comparator", "minecraft:comparator"]);
		$this->register(DaylightSensor::class, ["DaylightDetector", "minecraft:daylight_detector"]);
		$this->register(EnchantTable::class, ["EnchantTable", "minecraft:enchanting_table"]);
		$this->register(EnderChest::class, ["EnderChest", "minecraft:ender_chest"]);
		$this->register(FlowerPot::class, ["FlowerPot", "minecraft:flower_pot"]);
		$this->register(NormalFurnace::class, ["Furnace", "minecraft:furnace"]);
		$this->register(Hopper::class, ["Hopper", "minecraft:hopper"]);
		$this->register(ItemFrame::class, ["ItemFrame"]); //this is an entity in PC
		$this->register(Jukebox::class, ["Jukebox", "RecordPlayer", "minecraft:jukebox"]);
		$this->register(Lectern::class, ["Lectern", "minecraft:lectern"]);
		$this->register(MonsterSpawner::class, ["MobSpawner", "minecraft:mob_spawner"]);
		$this->register(Note::class, ["Music", "minecraft:noteblock"]);
		$this->register(ShulkerBox::class, ["ShulkerBox", "minecraft:shulker_box"]);
		$this->register(Sign::class, ["Sign", "minecraft:sign"]);
		$this->register(Smoker::class, ["Smoker", "minecraft:smoker"]);
		$this->register(SporeBlossom::class, ["SporeBlossom", "minecraft:spore_blossom"]);
		$this->register(MobHead::class, ["Skull", "minecraft:skull"]);
		$this->register(GlowingItemFrame::class, ["GlowItemFrame"]);

		//TODO: ChalkboardBlock
		//TODO: ChemistryTable
		//TODO: CommandBlock
		//TODO: Conduit
		//TODO: Dispenser
		//TODO: Dropper
		//TODO: EndGateway
		//TODO: EndPortal
		//TODO: JigsawBlock
		//TODO: MovingBlock
		//TODO: NetherReactor
		//TODO: PistonArm
		//TODO: StructureBlock
	}

	/**
	 * @param string[] $saveNames
	 * @phpstan-param class-string<Tile> $className
	 */
	public function register(string $className, array $saveNames = []) : void
	{
		Utils::testValidInstance($className, Tile::class);

		$shortName = (new \ReflectionClass($className))->getShortName();
		if (!in_array($shortName, $saveNames, true)) {
			$saveNames[] = $shortName;
		}

		foreach ($saveNames as $name) {
			$this->knownTiles[$name] = $className;
		}

		$this->saveNames[$className] = reset($saveNames);
	}

	/**
	 * @internal
	 * @throws SavedDataLoadingException
	 */
	public function createFromData(World $world, CompoundTag $nbt) : ?Tile
	{
		try {
			$type = $nbt->getString(Tile::TAG_ID, "");
			if (!isset($this->knownTiles[$type])) {
				return null;
			}
			$class = $this->knownTiles[$type];
			assert(is_a($class, Tile::class, true));
			/**
			 * @var Tile $tile
			 * @see Tile::__construct()
			 */
			$tile = new $class($world, new Vector3($nbt->getInt(Tile::TAG_X), $nbt->getInt(Tile::TAG_Y), $nbt->getInt(Tile::TAG_Z)));
			$tile->readSaveData($nbt);
		} catch (NbtException $e) {
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}

		return $tile;
	}

	/**
	 * @phpstan-param class-string<Tile> $class
	 */
	public function getSaveId(string $class) : string
	{
		if (isset($this->saveNames[$class])) {
			return $this->saveNames[$class];
		}
		throw new \InvalidArgumentException("Tile $class is not registered");
	}
}
