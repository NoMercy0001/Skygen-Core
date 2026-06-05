<?php

namespace Core\Tasks;

use Core\Managers\IslandManager;
use pocketmine\scheduler\Task;

class AutoSaveTask extends Task {

    public function __construct(
        private readonly IslandManager $islandManager
    ) {}

    public function onRun(): void {
        $this->islandManager->saveAll();
    }
}