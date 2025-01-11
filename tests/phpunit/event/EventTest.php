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

use PHPUnit\Framework\TestCase;
use watermossmc\event\fixtures\TestChildEvent;
use watermossmc\event\fixtures\TestGrandchildEvent;
use watermossmc\event\fixtures\TestParentEvent;
use watermossmc\plugin\Plugin;
use watermossmc\plugin\PluginManager;
use watermossmc\Server;

final class EventTest extends TestCase
{
	private Plugin $mockPlugin;
	private PluginManager $pluginManager;

	protected function setUp() : void
	{
		HandlerListManager::global()->unregisterAll();

		//TODO: this is a really bad hack and could break any time if PluginManager decides to access its Server field
		//we really need to make it possible to register events without a Plugin or Server context
		$mockServer = $this->createMock(Server::class);
		$this->mockPlugin = self::createStub(Plugin::class);
		$this->mockPlugin->method('isEnabled')->willReturn(true);

		$this->pluginManager = new PluginManager($mockServer, null);
	}

	public static function tearDownAfterClass() : void
	{
		HandlerListManager::global()->unregisterAll();
	}

	public function testHandlerInheritance() : void
	{
		$expectedOrder = [
			TestGrandchildEvent::class,
			TestChildEvent::class,
			TestParentEvent::class
		];
		$actualOrder = [];

		foreach ($expectedOrder as $class) {
			$this->pluginManager->registerEvent(
				$class,
				function (TestParentEvent $event) use (&$actualOrder, $class) : void {
					$actualOrder[] = $class;
				},
				EventPriority::NORMAL,
				$this->mockPlugin
			);
		}

		$event = new TestGrandchildEvent();
		$event->call();

		self::assertSame($expectedOrder, $actualOrder, "Expected event handlers to be called from most specific to least specific");
	}
}
