<?php

namespace Core\Tasks;

use Core\Managers\GeneratorManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class GeneratorTickTask extends Task {

    public function __construct(
        private readonly PluginBase $plugin,
        private readonly GeneratorManager $generatorManager
    ) {}


    public function onRun(): void {
        $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
        $this->generatorManager->tickGenerators($world);
    }
}