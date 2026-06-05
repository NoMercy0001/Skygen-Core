<?php

namespace Core\Listeners;

use Core\Main;
use Core\Managers\GeneratorManager;
use Core\Managers\IslandManager;
use Core\Managers\RegionManager;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\Listener;

final readonly class GeneratorListener implements Listener {

    public function __construct(
        private Main $plugin,
        private IslandManager $islandManager,
        private GeneratorManager $generatorManager,
        private RegionManager $regionManager
    ) {}

    public function onBlockForm(BlockFormEvent $event): void {
        // 1. Pobieramy blok, który ma powstać
        $block = $event->getBlock();
        $world = $block->getPosition()->getWorld();

        // 2. Szukamy wyspy na tych kordynatach
        $island = $this->islandManager->getIslandAt($block->getPosition());

        if ($island === null) {
            return; // To nie jest wyspa, zostawiamy standardowe generowanie
        }

        // 3. Sprawdzam rangę właściciela wyspy
        $ownerUuid = $island->getOwnerUuid();
        $rank = $this->plugin->getUsersConfig()->get($ownerUuid, "Gracz");

        // 4. Sprawdzam, czy wyspa jest w regionie
        $region = $this->regionManager->getRegionAt($block->getPosition());

        // Jeśli region istnieje, sprawdzam dostęp. Jeśli nie ma regionu - zakładam, że można generować.
        $canUse = !($region !== null) || $this->regionManager->canUseGenerator($rank, $region->requiredRank);

        // 4. Wywołuje generator z informacją, czy gracz ma dostęp do lepszego dropu
        // GeneratorManager sam zajmie się postawieniem bloku w zależności od $canUse
        $this->generatorManager->generateForIsland($island, $world, $canUse);
        $event->cancel();
    }
}