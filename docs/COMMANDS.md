# Built-in commands

| Command | Aliases | Permission | Supported syntax |
| --- | --- | --- | --- |
| `help` | — | Visitor | `/help [command]` |
| `list` | — | Visitor | `/list` |
| `tell` | `msg`, `w` | Member | `/tell <player> <message>` |
| `say` | — | Operator | `/say <message>` |
| `gamemode` | `gm` | Operator | `/gamemode <survival|creative|adventure> [player]` |
| `time` | — | Operator | `/time set <value>`, `/time add <value>`, `/time query <daytime|gametime|day>` |
| `tp` | `teleport` | Member | `/tp <player>`, `/tp <x> <y> <z>`, `/tp <player> <x> <y> <z>` |
| `op` | — | Operator | `/op <player>` |
| `deop` | — | Operator | `/deop <player>` |
| `stop` | — | Console only | `/stop` |

Coordinates in `/tp` accept absolute values and relative values such as `~` or
`~5`. Command feedback is English to match the server's existing console text.
