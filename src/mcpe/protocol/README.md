# MCPE protocol layout

- `clientbound/`: packets sent by the server to a Bedrock client.
- `serverbound/`: packets parsed from a Bedrock client.
- `handshake/`: packets used before the game session is established.
- `types/`: reusable protocol values and payload structures.
- `Packet.php` and `ProtocolInfo.php`: shared packet transport and protocol IDs.

When adding a packet, place it in the folder that matches its direction and
use the equivalent namespace. A packet used in both directions should stay in
the narrowest shared package, rather than being duplicated.

## Adding an inbound handler

Packets that need server-side behavior can be registered without editing the
central dispatcher:

```php
PacketHandler::registerInboundPacket(0x123, static function (
    string $packet,
    int $offset,
    Session $session,
    Socket $socket,
): bool {
    // Decode data starting at $offset.
    return true;
});
```

Returning `false` leaves processing to the built-in packet handler. Register a
handler during server or plugin startup, before clients connect.
