<?php

namespace Core\Listeners;

use Core\Managers\SkyGenManager;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\Listener;

final class GeneratorListener implements Listener {

    public function __construct(
        private readonly SkyGenManager $skyGenManager
    ) {}

    public function onBlockForm(BlockFormEvent $event): void {
        // 1. Pobieramy blok, który ma powstać
        $block = $event->getBlock();
        $world = $block->getPosition()->getWorld();

        // 2. Szukamy wyspy na tych kordynatach
        $island = $this->skyGenManager->islandManager->getIslandAt($block->getPosition());

        if ($island === null) {
            return; // To nie jest wyspa, zostawiamy standardowe generowanie
        }

        // 3. Decydujemy o rozwoju bloku na podstawie logiki SkyGen
        $blockId = $this->skyGenManager->generatorManager->generateForIsland($island);

        // 4. Podmieniamy blok (dla uproszczenia zwracamy ID, zamień na odpowiedni obiekt bloku)
        // Jeśli generateForIsland zwraca np, ID bloku diamentu:
        if ($blockId !== 1) { // 1 to Stone
            $world->setBlock($block->getPosition(), $blockId);
        }
    }
}