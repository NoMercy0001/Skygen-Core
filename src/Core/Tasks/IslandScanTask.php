<?php

namespace Core\Tasks;

use Core\Island;
use Core\Managers\IslandManager;
use Core\Managers\UpgradeManager;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class IslandScanTask extends Task {

    public function __construct(
        private readonly IslandManager $islandManager,
        private readonly UpgradeManager $upgradeManager,
    ) {}

    public function onRun(): void {
        // 1. Pobieram wszystkie wyspy z IslandManager
        $islands = $this->islandManager->getAllIslands();

        /** @var Island[] $islands */
        foreach ($islands as $island) {
            $ownerUuid = $island->getOwnerUuid();
            $currentLevel = $island->getLevel();

            // 2. Używam UpgradeManagera
            $realLevel = $this->upgradeManager->getUpgradeLevel($ownerUuid, "level");

            // 3. Jeśli poziomy się różnią, synchronizuje obiekt w pamięci
            if ($currentLevel !== $realLevel) {
                $island->setLevel($realLevel);
            }

            // 4. Dodatkowo: weryfikuję generatora (metoda z klasy Island)
            $pos = $island->getGeneratorPos();
            if ($pos->getY() < 1) {
                $center = $island->getCenter();
                $newSafePos = new Vector3(
                    $center->getX(),
                    $center->getY() + 5,
                    $center->getZ()
                );

                $island->setGeneratorPos($newSafePos);
            }
        }
    }
}