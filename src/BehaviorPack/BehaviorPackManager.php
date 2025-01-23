<?php

namespace WatermossMC\BehaviorPack;

class BehaviorPackManager
{
    private $behaviorPacks = [];

    public function __construct(string $behaviorPackDir)
    {
        echo "Loading Behavior Packs...\n";

        foreach (glob($behaviorPackDir . '/*.json') as $manifestFile) {
            $this->behaviorPacks[] = basename($manifestFile);
            echo "Loaded Behavior Pack: " . basename($manifestFile) . "\n";
        }
    }

    public function getBehaviorPacks(): array
    {
        return $this->behaviorPacks;
    }
}
