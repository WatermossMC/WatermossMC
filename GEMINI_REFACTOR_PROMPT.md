# Task: Split the MCPE packet dispatcher

You are working in the WatermossMC PHP repository. Refactor the monolithic
`src/mcpe/PacketHandler.php` into small, testable packet-phase handlers without
changing Minecraft Bedrock protocol behavior.

## Current state

- PHP 8.1+ and Composer PSR-4 autoloading are used.
- MCPE packet classes are organized in:
  - `src/mcpe/protocol/clientbound/`
  - `src/mcpe/protocol/serverbound/`
  - `src/mcpe/protocol/handshake/`
  - `src/mcpe/protocol/types/`
- `PacketHandler` is currently the working static dispatcher used by:
  - `server.php` via `PacketHandler::setServer($server)`
  - `src/mcpe/network/RakNet.php` via `PacketHandler::handleBatch(...)`
  - `src/Server.php` via `PacketHandler::syncPlayers()`
- `InboundPacketRegistry` already exists for optional custom inbound packet
  handlers. Preserve its public API unless there is a compelling compatibility
  reason to change it.

## Goal

Make `PacketHandler` a small interface for a single inbound packet handler:

```php
interface PacketHandler
{
    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool;
}
```

Create a thin `PacketDispatcher` that retains the current static public entry
points (`setServer`, `handleBatch`, `syncPlayers`, and inbound registry access)
and routes packet IDs to phase handlers.

Create concrete handlers under `src/mcpe/handler/`:

- `DisconnectPacketHandler`
- `NetworkSettingsPacketHandler`
- `LoginPacketHandler`
- `HandshakePacketHandler`
- `ResourcePackPacketHandler`
- `InGamePacketHandler`

## Routing ownership

- Disconnect: disconnect packet and player/session cleanup.
- Network settings: request-network-settings and compression/state setup.
- Login: login parsing, identity validation, key generation, JWT creation, and
  pending encryption setup.
- Handshake: client-to-server handshake acknowledgement and encryption
  finalization.
- Resource pack: resource pack response states and transition to play setup.
- In-game: client cache status, chunk radius, movement, chat/text, and command
  request packets.

`PacketDispatcher` must not contain packet-specific `switch` case bodies after
the refactor. It may map packet IDs to handlers and own shared server/world
dependencies.

## No hardcoded packet list

Do not replace the existing `switch` with another hardcoded `match`, fixed ID
array, or fixed packet-class import list in `PacketDispatcher`. Each phase
handler must declaratively expose the packet IDs it accepts (for example with a
`packetIds(): array` route-provider method or PHP attributes). At startup, the
dispatcher registers those declarations in a built-in packet registry and only
performs a registry lookup for each decoded ID.

Keep protocol numbers in `ProtocolInfo`, since they are Bedrock specification
constants. Adding a packet must only require adding its handler/route metadata,
not editing dispatcher routing code. Duplicate IDs must fail fast unless an
explicit override is requested.

## No hardcoded packet payloads

The payload layout inside packet classes must also become declarative. Do not
leave repeated sequences such as `writeVarInt()`, `writeString()`, byte offsets,
or matching `read*()` calls hardcoded as ad-hoc field order in every packet.

Introduce a reusable packet schema/codec layer, for example under
`src/mcpe/protocol/schema/`, with:

- a `PacketSchema` that declares ordered fields;
- reusable field codecs such as `VarInt`, `VarLong`, `String`, `Bool`, `Float`,
  `Vector2`, `Vector3`, `Uuid`, `Nbt`, and conditional/array fields;
- generic `encode(array|object $data): string` and
  `decode(string $payload, int &$offset): array` operations;
- named field data, so handlers use `$data['radius']` instead of manually
  advancing offsets.

Each packet class should declare its packet ID and schema, then delegate
encoding/decoding to the shared codec. Keep custom codec implementations only
where Bedrock has a genuinely irregular payload; document the reason beside the
packet. Preserve exact field order, signedness, endianness, and conditional
fields from the current protocol behavior.

## Design constraints

1. Preserve session-state ordering exactly (`MC_NONE`, `MC_NETWORK`,
   `MC_LOGIN`, `MC_RESOURCE`, `MC_PRESPAWN`, and play state).
2. Do not change packet IDs, binary parsing, encryption, compression, or
   RakNet flush timing unless fixing a demonstrable bug.
3. Keep dependencies explicit through constructor injection. Pass narrowly
   scoped callbacks for dispatcher-only actions such as `startPlay()` or chunk
   sending rather than making handlers depend on global mutable state.
4. The custom `InboundPacketRegistry` should run before built-in handlers, as
   it does now. A handler returning `true` consumes the packet.
   Built-in handlers must use the same predictable registry model.
5. Do not add aliases that make both `PacketHandler` interface and a class of
   the same name coexist. Update all old static call sites to `PacketDispatcher`.
6. Preserve the organized protocol namespaces and update imports to their
   `clientbound`, `serverbound`, or `handshake` packages.
7. Keep changes focused. Do not alter Composer dependencies or unrelated code.

## Suggested implementation order

1. Add the `PacketHandler` interface and rename the current static class to
   `PacketDispatcher`.
2. Update all call sites and confirm syntax checks pass.
3. Extract `DisconnectPacketHandler` and route its ID.
4. Extract network settings, login, and handshake in that order. Verify the
   encryption state transition after each extraction.
5. Extract resource-pack and in-game handlers.
6. Remove all packet-specific logic and fixed packet-ID lists from the
   dispatcher, leaving only registry lookup and shared lifecycle methods.

## Verification required

Run all available checks after the refactor:

```bash
find src server.php -type f -name '*.php' -print0 | xargs -0 -n1 php -l
composer analyse
```

Also search for stale references:

```bash
grep -R -F 'PacketHandler::' -n --include='*.php' src server.php
grep -R -F 'PacketDispatcher' -n --include='*.php' src server.php
```

Report the files moved/created, the packet IDs owned by each handler, and any
behavior that could not be verified without a Bedrock client integration test.
