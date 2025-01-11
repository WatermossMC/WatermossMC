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

namespace watermossmc\entity;

use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\data\bedrock\LegacyEntityIdToStringIdMap;
use watermossmc\data\bedrock\PotionTypeIdMap;
use watermossmc\data\bedrock\PotionTypeIds;
use watermossmc\data\SavedDataLoadingException;
use watermossmc\entity\EntityDataHelper as Helper;
use watermossmc\entity\object\EndCrystal;
use watermossmc\entity\object\ExperienceOrb;
use watermossmc\entity\object\FallingBlock;
use watermossmc\entity\object\ItemEntity;
use watermossmc\entity\object\Painting;
use watermossmc\entity\object\PaintingMotive;
use watermossmc\entity\object\PrimedTNT;
use watermossmc\entity\projectile\Arrow;
use watermossmc\entity\projectile\Egg;
use watermossmc\entity\projectile\EnderPearl;
use watermossmc\entity\projectile\ExperienceBottle;
use watermossmc\entity\projectile\IceBomb;
use watermossmc\entity\projectile\Snowball;
use watermossmc\entity\projectile\SplashPotion;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\nbt\NbtException;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\ShortTag;
use watermossmc\nbt\tag\StringTag;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;
use watermossmc\world\World;

use function count;
use function reset;

/**
 * This class manages the creation of entities loaded from disk.
 * You need to register your entity into this factory if you want to load/save your entity on disk (saving with chunks).
 */
final class EntityFactory
{
	use SingletonTrait;

	public const TAG_IDENTIFIER = "identifier"; //TAG_String
	public const TAG_LEGACY_ID = "id"; //TAG_Int

	/**
	 * @var \Closure[] save ID => creator function
	 * @phpstan-var array<int|string, \Closure(World, CompoundTag) : Entity>
	 */
	private array $creationFuncs = [];
	/**
	 * @var string[]
	 * @phpstan-var array<class-string<Entity>, string>
	 */
	private array $saveNames = [];

	public function __construct()
	{
		//define legacy save IDs first - use them for saving for maximum compatibility with Minecraft PC
		//TODO: index them by version to allow proper multi-save compatibility

		$this->register(Arrow::class, function (World $world, CompoundTag $nbt) : Arrow {
			return new Arrow(Helper::parseLocation($nbt, $world), null, $nbt->getByte(Arrow::TAG_CRIT, 0) === 1, $nbt);
		}, ['Arrow', 'minecraft:arrow']);

		$this->register(Egg::class, function (World $world, CompoundTag $nbt) : Egg {
			return new Egg(Helper::parseLocation($nbt, $world), null, $nbt);
		}, ['Egg', 'minecraft:egg']);

		$this->register(EndCrystal::class, function (World $world, CompoundTag $nbt) : EndCrystal {
			return new EndCrystal(Helper::parseLocation($nbt, $world), $nbt);
		}, ['EnderCrystal', 'minecraft:ender_crystal']);

		$this->register(EnderPearl::class, function (World $world, CompoundTag $nbt) : EnderPearl {
			return new EnderPearl(Helper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownEnderpearl', 'minecraft:ender_pearl']);

		$this->register(ExperienceBottle::class, function (World $world, CompoundTag $nbt) : ExperienceBottle {
			return new ExperienceBottle(Helper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownExpBottle', 'minecraft:xp_bottle']);

		$this->register(ExperienceOrb::class, function (World $world, CompoundTag $nbt) : ExperienceOrb {
			$value = 1;
			if (($valuePcTag = $nbt->getTag(ExperienceOrb::TAG_VALUE_PC)) instanceof ShortTag) { //PC
				$value = $valuePcTag->getValue();
			} elseif (($valuePeTag = $nbt->getTag(ExperienceOrb::TAG_VALUE_PE)) instanceof IntTag) { //PE save format
				$value = $valuePeTag->getValue();
			}

			return new ExperienceOrb(Helper::parseLocation($nbt, $world), $value, $nbt);
		}, ['XPOrb', 'minecraft:xp_orb']);

		$this->register(FallingBlock::class, function (World $world, CompoundTag $nbt) : FallingBlock {
			return new FallingBlock(Helper::parseLocation($nbt, $world), FallingBlock::parseBlockNBT(RuntimeBlockStateRegistry::getInstance(), $nbt), $nbt);
		}, ['FallingSand', 'minecraft:falling_block']);

		$this->register(IceBomb::class, function (World $world, CompoundTag $nbt) : IceBomb {
			return new IceBomb(Helper::parseLocation($nbt, $world), null, $nbt);
		}, ['minecraft:ice_bomb']);

		$this->register(ItemEntity::class, function (World $world, CompoundTag $nbt) : ItemEntity {
			$itemTag = $nbt->getCompoundTag(ItemEntity::TAG_ITEM);
			if ($itemTag === null) {
				throw new SavedDataLoadingException("Expected \"" . ItemEntity::TAG_ITEM . "\" NBT tag not found");
			}

			$item = Item::nbtDeserialize($itemTag);
			if ($item->isNull()) {
				throw new SavedDataLoadingException("Item is invalid");
			}
			return new ItemEntity(Helper::parseLocation($nbt, $world), $item, $nbt);
		}, ['Item', 'minecraft:item']);

		$this->register(Painting::class, function (World $world, CompoundTag $nbt) : Painting {
			$motive = PaintingMotive::getMotiveByName($nbt->getString(Painting::TAG_MOTIVE));
			if ($motive === null) {
				throw new SavedDataLoadingException("Unknown painting motive");
			}
			$blockIn = new Vector3($nbt->getInt(Painting::TAG_TILE_X), $nbt->getInt(Painting::TAG_TILE_Y), $nbt->getInt(Painting::TAG_TILE_Z));
			if (($directionTag = $nbt->getTag(Painting::TAG_DIRECTION_BE)) instanceof ByteTag) {
				$facing = Painting::DATA_TO_FACING[$directionTag->getValue()] ?? Facing::NORTH;
			} elseif (($facingTag = $nbt->getTag(Painting::TAG_FACING_JE)) instanceof ByteTag) {
				$facing = Painting::DATA_TO_FACING[$facingTag->getValue()] ?? Facing::NORTH;
			} else {
				throw new SavedDataLoadingException("Missing facing info");
			}

			return new Painting(Helper::parseLocation($nbt, $world), $blockIn, $facing, $motive, $nbt);
		}, ['Painting', 'minecraft:painting']);

		$this->register(PrimedTNT::class, function (World $world, CompoundTag $nbt) : PrimedTNT {
			return new PrimedTNT(Helper::parseLocation($nbt, $world), $nbt);
		}, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt']);

		$this->register(Snowball::class, function (World $world, CompoundTag $nbt) : Snowball {
			return new Snowball(Helper::parseLocation($nbt, $world), null, $nbt);
		}, ['Snowball', 'minecraft:snowball']);

		$this->register(SplashPotion::class, function (World $world, CompoundTag $nbt) : SplashPotion {
			$potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort(SplashPotion::TAG_POTION_ID, PotionTypeIds::WATER));
			if ($potionType === null) {
				throw new SavedDataLoadingException("No such potion type");
			}
			return new SplashPotion(Helper::parseLocation($nbt, $world), null, $potionType, $nbt);
		}, ['ThrownPotion', 'minecraft:potion', 'thrownpotion']);

		$this->register(Squid::class, function (World $world, CompoundTag $nbt) : Squid {
			return new Squid(Helper::parseLocation($nbt, $world), $nbt);
		}, ['Squid', 'minecraft:squid']);

		$this->register(Villager::class, function (World $world, CompoundTag $nbt) : Villager {
			return new Villager(Helper::parseLocation($nbt, $world), $nbt);
		}, ['Villager', 'minecraft:villager']);

		$this->register(Zombie::class, function (World $world, CompoundTag $nbt) : Zombie {
			return new Zombie(Helper::parseLocation($nbt, $world), $nbt);
		}, ['Zombie', 'minecraft:zombie']);

		$this->register(Human::class, function (World $world, CompoundTag $nbt) : Human {
			return new Human(Helper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
		}, ['Human']);
	}

	/**
	 * Registers an entity type into the index.
	 *
	 * @param string   $className Class that extends Entity
	 * @param string[] $saveNames An array of save names which this entity might be saved under.
	 * @phpstan-param class-string<Entity> $className
	 * @phpstan-param list<string> $saveNames
	 * @phpstan-param \Closure(World $world, CompoundTag $nbt) : Entity $creationFunc
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className, \Closure $creationFunc, array $saveNames) : void
	{
		if (count($saveNames) === 0) {
			throw new \InvalidArgumentException("At least one save name must be provided");
		}
		Utils::testValidInstance($className, Entity::class);
		Utils::validateCallableSignature(new CallbackType(
			new ReturnType(Entity::class),
			new ParameterType("world", World::class),
			new ParameterType("nbt", CompoundTag::class)
		), $creationFunc);

		foreach ($saveNames as $name) {
			$this->creationFuncs[$name] = $creationFunc;
		}

		$this->saveNames[$className] = reset($saveNames);
	}

	/**
	 * Creates an entity from data stored on a chunk.
	 *
	 * @throws SavedDataLoadingException
	 * @internal
	 */
	public function createFromData(World $world, CompoundTag $nbt) : ?Entity
	{
		try {
			$saveId = $nbt->getTag(self::TAG_IDENTIFIER) ?? $nbt->getTag(self::TAG_LEGACY_ID);
			$func = null;
			if ($saveId instanceof StringTag) {
				$func = $this->creationFuncs[$saveId->getValue()] ?? null;
			} elseif ($saveId instanceof IntTag) { //legacy MCPE format
				$stringId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($saveId->getValue() & 0xff);
				$func = $stringId !== null ? $this->creationFuncs[$stringId] ?? null : null;
			}
			if ($func === null) {
				return null;
			}
			/** @var Entity $entity */
			$entity = $func($world, $nbt);

			return $entity;
		} catch (NbtException $e) {
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}
	}

	public function injectSaveId(string $class, CompoundTag $saveData) : void
	{
		if (isset($this->saveNames[$class])) {
			$saveData->setTag(self::TAG_IDENTIFIER, new StringTag($this->saveNames[$class]));
		} else {
			throw new \InvalidArgumentException("Entity $class is not registered");
		}
	}

	/**
	 * @phpstan-param class-string<Entity> $class
	 */
	public function getSaveId(string $class) : string
	{
		if (isset($this->saveNames[$class])) {
			return $this->saveNames[$class];
		}
		throw new \InvalidArgumentException("Entity $class is not registered");
	}
}
