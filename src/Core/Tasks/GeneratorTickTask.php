<?php

namespace Core\Tasks;

use Core\Managers\GeneratorManager;
use Core\Managers\SkyGenManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class GeneratorTickTask extends Task {

    public function __construct(
        private readonly SkyGenManager $skyGenManager,
        private readonly PluginBase $plugin,
        private readonly GeneratorManager $generatorManager
    ) {}


    public function onRun(): void {
        $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
        $this->skyGenManager->tickGenerators($world);
        $this->generatorManager->tickGenerators($world);
    }
}