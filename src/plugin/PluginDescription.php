<?php

declare(strict_types=1);

namespace watermossmc\plugin;

use RuntimeException;

final class PluginDescription
{
    /**
     * @param array<string, mixed> $extra
     */
    private function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $main,
        public readonly string $api,
        public readonly array $extra = []
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $source): self
    {
        foreach (['name', 'version', 'main'] as $field) {
            if (!isset($data[$field]) || !\is_string($data[$field]) || trim($data[$field]) === '') {
                throw new RuntimeException("Invalid plugin manifest {$source}: missing {$field}");
            }
        }

        $api = isset($data['api']) && \is_string($data['api']) ? $data['api'] : '1.0.0';

        return new self(
            trim($data['name']),
            trim($data['version']),
            trim($data['main']),
            trim($api),
            $data
        );
    }
}
