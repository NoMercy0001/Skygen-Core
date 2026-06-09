<?php

namespace Core;

use Core\Listeners\CombatListener;
use Core\Listeners\GeneratorListener;
use Core\Listeners\RankListener;
use Core\Listeners\RegionListener;
use Core\Tasks\AutoSaveTask;
use Core\Tasks\GeneratorTickTask;
use Core\Tasks\IslandScanTask;

final readonly class Bootstrap {

    public function __construct(
        private Main $plugin,
        private Container  $container
    ) {}

    public function registerManagers(): void {
        // 1. Wczytywanie danych z bazy/pliku do IslandManagera innych managerow.
        $this->container->islandManager->loadAllIslandsFromDatabase();
        $this->container->regionManager->loadRegionsFromDatabase();
    }

    public function registerListeners(): void {
        // Rejestrujemy nasz GeneratorListener, wstrzykując do niego SkyGenManager
        $this->plugin->getServer()->getPluginManager()->registerEvents(
            new GeneratorListener($this->plugin,$this->container->islandManager,$this->container->generatorManager, $this->container->regionManager),
            $this->plugin
        );
        $this->plugin->getServer()->getPluginManager()->registerEvents(
            new RegionListener($this->plugin, $this->container->regionManager, $this->container->combatManager, $this->container->config, $this->container->rankManager), $this->plugin
        );
        $this->plugin->getServer()->getPluginManager()->registerEvents(
            new CombatListener($this->container->combatManager), $this->plugin
        );
        $this->plugin->getServer()->getPluginManager()->registerEvents(
          new RankListener($this->container->rankManager, $this->plugin), $this->plugin
        );
    }

    public function registerTasks(): void {
        $scheduler = $this->plugin->getScheduler();

        // Uruchamiam zadania co określoną liczbę ticków (np. 1200 ticków = 1 minuta, 100 ticków = 5 sekund)
        $scheduler->scheduleRepeatingTask(new AutoSaveTask($this->container->islandManager), 1200);
        $scheduler->scheduleRepeatingTask(new GeneratorTickTask($this->plugin, $this->container->generatorManager), 100);
        $scheduler->scheduleRepeatingTask(new IslandScanTask($this->container->islandManager, $this->container->upgradeManager), 100);
    }
}