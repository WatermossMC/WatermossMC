<?php

namespace WatermossMC\ResourcePack;

class ResourcePackManager
{
    private $resourcePacks = [];

    public function __construct(string $resourcePackDir)
    {
        echo "Loading Resource Packs...\n";

        foreach (glob($resourcePackDir . '/*.json') as $manifestFile) {
            $this->resourcePacks[] = basename($manifestFile);
            echo "Loaded Resource Pack: " . basename($manifestFile) . "\n";
        }
    }

    public function getResourcePacks(): array
    {
        return $this->resourcePacks;
    }
}
