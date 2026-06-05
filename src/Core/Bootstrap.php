<?php

namespace Core;

use Core\Listeners\GeneratorListener;
use Core\Tasks\AutoSaveTask;
use Core\Tasks\GeneratorTickTask;
use Core\Tasks\IslandScanTask;
use pocketmine\plugin\PluginBase;

final class Bootstrap {

    public function __construct(
        private readonly PluginBase $plugin,
        private readonly Container $container
    ) {}

    public function registerManagers(): void {
        // 1. Wczytywanie konfiguracji z pliku config.yml
        $config = $this->plugin->getConfig();

        // 2. Wczytywanie danych z bazy/pliku do IslandManagera
        $this->container->islandManager->loadAllIslandsFromDatabase();

    }

    public function registerListeners(): void {
        // Rejestrujemy nasz GeneratorListener, wstrzykując do niego SkyGenManager
        $this->plugin->getServer()->getPluginManager()->registerEvents(
            new GeneratorListener($this->container->skyGenManager),
            $this->plugin
        );
    }

    public function registerTasks(): void {
        $scheduler = $this->plugin->getScheduler();

        // Uruchamiam zadania co określoną liczbę ticków (np. 1200 ticków = 1 minuta, 100 ticków = 5 sekund)
        $scheduler->scheduleRepeatingTask(new AutoSaveTask($this->container->islandManager), 1200);
        $scheduler->scheduleRepeatingTask(new GeneratorTickTask($this->container->skyGenManager, $this->plugin, $this->container->generatorManager), 100);
        $scheduler->scheduleRepeatingTask(new IslandScanTask($this->container->islandManager, $this->container->upgradeManager), 100);
    }
}