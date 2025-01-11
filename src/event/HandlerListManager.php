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

namespace watermossmc\event;

use watermossmc\plugin\Plugin;
use watermossmc\utils\Utils;

class HandlerListManager
{
	private static ?self $globalInstance = null;

	public static function global() : self
	{
		return self::$globalInstance ?? (self::$globalInstance = new self());
	}

	/** @var HandlerList[] classname => HandlerList */
	private array $allLists = [];
	/**
	 * @var RegisteredListenerCache[] event class name => cache
	 * @phpstan-var array<class-string<Event>, RegisteredListenerCache>
	 */
	private array $handlerCaches = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 */
	public function unregisterAll(RegisteredListener|Plugin|Listener|null $object = null) : void
	{
		if ($object instanceof Listener || $object instanceof Plugin || $object instanceof RegisteredListener) {
			foreach ($this->allLists as $h) {
				$h->unregister($object);
			}
		} else {
			foreach ($this->allLists as $h) {
				$h->clear();
			}
		}
	}

	/**
	 * @phpstan-param \ReflectionClass<Event> $class
	 */
	private static function isValidClass(\ReflectionClass $class) : bool
	{
		$tags = Utils::parseDocComment((string) $class->getDocComment());
		return !$class->isAbstract() || isset($tags["allowHandle"]);
	}

	/**
	 * @phpstan-param \ReflectionClass<Event> $class
	 *
	 * @phpstan-return \ReflectionClass<Event>|null
	 */
	private static function resolveNearestHandleableParent(\ReflectionClass $class) : ?\ReflectionClass
	{
		for ($parent = $class->getParentClass(); $parent !== false; $parent = $parent->getParentClass()) {
			if (self::isValidClass($parent)) {
				return $parent;
			}
			//NOOP
		}
		return null;
	}

	/**
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @phpstan-param class-string<covariant Event> $event
	 *
	 * @throws \ReflectionException
	 * @throws \InvalidArgumentException
	 */
	public function getListFor(string $event) : HandlerList
	{
		if (isset($this->allLists[$event])) {
			return $this->allLists[$event];
		}

		$class = new \ReflectionClass($event);
		if (!self::isValidClass($class)) {
			throw new \InvalidArgumentException("Event must be non-abstract or have the @allowHandle annotation");
		}

		$parent = self::resolveNearestHandleableParent($class);
		$cache = new RegisteredListenerCache();
		$this->handlerCaches[$event] = $cache;
		return $this->allLists[$event] = new HandlerList(
			$event,
			parentList: $parent !== null ? $this->getListFor($parent->getName()) : null,
			handlerCache: $cache
		);
	}

	/**
	 * @phpstan-param class-string<covariant Event> $event
	 *
	 * @return RegisteredListener[]
	 */
	public function getHandlersFor(string $event) : array
	{
		$cache = $this->handlerCaches[$event] ?? null;
		//getListFor() will populate the cache for the next call
		return $cache?->list ?? $this->getListFor($event)->getListenerList();
	}

	/**
	 * @return HandlerList[]
	 */
	public function getAll() : array
	{
		return $this->allLists;
	}
}
