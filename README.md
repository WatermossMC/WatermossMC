# WatermossMC

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%5E8.1-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Minecraft-Bedrock-57A1E3?style=for-the-badge&logo=minecraft&logoColor=white" alt="Minecraft Bedrock">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge" alt="License">
</p>

WatermossMC is a modular Minecraft Bedrock Edition server software written in PHP. Built to handle custom packet dispatching, RakNet networking, block states, and entity management.

---

## ✨ Features

- **Bedrock Protocol & RakNet**: Robust network session handling, packet serialization/deserialization, and UDP server implementation.
- **World & Chunk Management**: Fast chunk and sub-chunk handling with support for block state registries and runtimes.
- **Entity & Item Systems**: Comprehensive entity attributes, metadata flags, inventories, and item registries.
- **Event-Driven Architecture**: Clean event dispatcher supporting player interactions, chat, movement, world loading, and server lifecycle hooks.
- **Plugin System**: Flexible plugin manager allowing developers to extend server functionality easily.
- **Command Framework**: Built-in command parsing and registry with standard administration and utility commands (`/gamemode`, `/teleport`, `/help`, `/op`, etc.).
- **Xbox Authentication & Crypto**: Built-in cryptographic helpers and Xbox auth verification.

---

## 📋 Requirements

- **PHP 8.1** or higher (PHP 8.2+ recommended)
- Required PHP extensions:
  - `ext-sockets`
  - `ext-json`
  - `ext-mbstring`
  - `ext-openssl`
- **Composer** for dependency management

---

## 🚀 Getting Started

1. **Clone the repository**:
   ```bash
   git clone https://github.com/watermossmc/WatermossMC.git
   cd WatermossMC
   ```

2. **Install dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Run the server**:
   ```bash
   php server.php
   ```
   *(Or build a PHAR package using `composer build-phar`)*

---

## ⚙️ Configuration

You can customize your server settings in `server.properties` located in the root directory (ports, max players, gamemode, default world settings, etc.).

---

## 📖 Documentation

Check out the [docs/](docs/) folder for more details:
- [Commands Guide](docs/COMMANDS.md)
- [Plugin API](docs/PLUGIN_API.md)

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/watermossmc/WatermossMC/issues).

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
