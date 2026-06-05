<?php

namespace Core\Managers;

use pocketmine\world\World;

final class SkyGenManager {

    public function __construct(
        public readonly IslandManager $islandManager,
        public readonly GeneratorManager $generatorManager,
        public readonly UpgradeManager $upgradeManager,
        public readonly EconomyManager $economyManager
    ) {}

    public function tickGenerators(World $world): void {
        // Pobieram wszystkie wyspy
        // Dla każdej wyspy sprawdzam, czy trzeba wygenerować blok
        foreach ($this->islandManager->getAllIslands() as $island) {
            // Logika generowania:
            $this->generatorManager->generateForIsland($island, $world);

        }
    }
}