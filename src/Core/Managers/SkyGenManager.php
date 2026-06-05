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
}