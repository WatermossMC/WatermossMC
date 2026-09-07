# Plugin API

WatermossMC plugins live in `plugins/<PluginName>/`. A plugin needs a `plugin.json`
manifest and a main class that extends `watermossmc\plugin\PluginBase`.

```json
{
  "name": "Welcome",
  "version": "1.0.0",
  "main": "example\\WelcomePlugin",
  "api": "1.0.0",
  "author": "Your name"
}
```

The main class may be stored at either `src/example/WelcomePlugin.php` or
`example/WelcomePlugin.php`.

```php
<?php

declare(strict_types=1);

namespace example;

use watermossmc\command\CallbackCommand;
use watermossmc\event\PlayerJoinEvent;
use watermossmc\plugin\PluginBase;

final class WelcomePlugin extends PluginBase
{
    public function onLoad(): void
    {
        $this->setConfigDefaults(['welcome-message' => 'Welcome to the server!']);
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig(); // Copies resources/config.json if supplied.

        $this->on(PlayerJoinEvent::class, function (PlayerJoinEvent $event): void {
            $event->player->sendMessage($this->getConfig()->getString('welcome-message'));
        });

        $this->registerCommand(new CallbackCommand(
            'welcome',
            'Shows the configured welcome message',
            '/welcome',
            function (mixed $sender, array $args): void {
                if ($sender instanceof \watermossmc\player\Player) {
                    $sender->sendMessage($this->getConfig()->getString('welcome-message'));
                }
            },
            aliases: ['wmwelcome'],
        ));
    }
}
```

## Lifecycle and cleanup

`onLoad()` runs while the plugin is discovered. `onEnable()` runs after required
dependencies are enabled, and `onDisable()` runs during shutdown or when the
plugin is disabled. `depend` in `plugin.json` is enabled first; `softdepend` is
metadata only.

Listeners, scheduled tasks, and commands registered through `PluginBase` are
automatically removed when the plugin is disabled—even when `onEnable()` or
`onDisable()` throws. Use the returned listener ID with `off()` when a listener
must be removed earlier.

## API map

Keep plugin code organized by using the API groups below:

| Need | API |
| --- | --- |
| Server and players | `getServer()`, `broadcastMessage()`, `getServer()->getPlayer()` |
| Lifecycle | `onLoad()`, `onEnable()`, `onDisable()`, `isEnabled()` |
| Events | `on()`, `off()` (also available as `listen()` and `unlisten()`) |
| Scheduled work | `scheduleDelayedTask()`, `scheduleRepeatingTask()`, `cancelTask()` |
| Commands | `registerCommand()`, `unregisterCommand()`, `CallbackCommand` |
| Data | `getDataFolder()`, `getConfig()`, `saveConfig()`, `reloadConfig()` |
| Plugin metadata | `getName()`, `getVersion()`, `getDescription()` |

Use the `PluginManager` for central administration: `getPlugin()`,
`getPlugins()`, `enablePlugin()`, `disablePlugin()`, and `isPluginEnabled()`.

## Config

`getConfig()` reads and writes `<plugin>/data/config.json`. The config supports
`get()`, `getString()`, `getInt()`, `getBool()`, `set()`, `remove()`,
`reload()`, and `save()`. Defaults set through `setConfigDefaults()` are used
for missing values on load. Call `saveConfig()` to persist changes.

## Commands

Use `CallbackCommand` for small plugin commands, or extend `Command` for a
reusable command class. A command's primary name and aliases are reserved as a
single unit: a duplicate registration fails instead of silently replacing an
existing command. Commands can be unregistered by either their primary name or
an alias. The dispatcher accepts an optional leading `/` and supports quoted
arguments, for example `/say "hello world"` passes `hello world` as one argument.
