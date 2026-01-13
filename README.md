# WatermossMC - Minimal PHP Bedrock Server

Server Minecraft Bedrock Edition minimal yang ditulis dalam PHP murni.

## Fitur

- ✅ RakNet protocol implementation
- ✅ Packet handling untuk Minecraft Bedrock
- ✅ Player management
- ✅ World dan chunk system
- ✅ Graceful shutdown
- ✅ Environment-based configuration
- ✅ Debug logging control

## Persyaratan

- PHP 8.1+
- Composer

## Instalasi

1. Install dependencies:
```bash
composer install
```

2. Jalankan server:
```bash
php server.php
```

## Konfigurasi

Server dapat dikonfigurasi melalui environment variables:

```bash
# Server binding
export SERVER_IP=0.0.0.0
export SERVER_PORT=19132

# Server settings
export MAX_PLAYERS=20
export MOTD="My Minecraft Server"

# Debug settings
export DEBUG=0  # Set to 1 untuk enable debug logs
```

Atau buat file `.env` berdasarkan `.env.example`.

## Menjalankan

```bash
# Default (debug enabled)
php server.php

# Production mode (debug disabled)
DEBUG=0 php server.php

# Custom port
SERVER_PORT=25565 php server.php
```

## Shutdown

Server mendukung graceful shutdown dengan signal:
- `Ctrl+C` (SIGINT)
- `kill -TERM <pid>` (SIGTERM)

## Struktur Kode

```
src/
├── Binary/          # Utility biner
├── Crypto/          # Enkripsi koneksi
├── Minecraft/       # Logika game
│   ├── Packets/     # Semua packet Minecraft
│   └── NBT/         # Named Binary Tag
├── Network/         # Lapisan jaringan (RakNet)
└── Util/            # Utilities (Logger, MOTD)
```

## Perbaikan Terbaru

- ✅ Fixed OpenSSL compatibility issue
- ✅ Added graceful shutdown handling
- ✅ Implemented proper tick loop with heartbeat
- ✅ Added environment-based configuration
- ✅ Improved error handling
- ✅ Added debug logging control