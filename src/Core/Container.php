<?php

namespace Core;

use Core\Managers\CombatManager;
use Core\Managers\EconomyManager;
use Core\Managers\GeneratorManager;
use Core\Managers\IslandManager;
use Core\Managers\RegionManager;
use Core\Managers\SkyGenManager;
use Core\Managers\UpgradeManager;
use pocketmine\plugin\PluginBase;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

final class Container {

    public readonly IslandManager $islandManager;
    public readonly UpgradeManager $upgradeManager;
    public readonly EconomyManager $economyManager;
    public readonly GeneratorManager $generatorManager;
    public readonly SkyGenManager $skyGenManager;
    public readonly CombatManager $combatManager;
    public readonly RegionManager $regionManager;

    private DataConnector $databaseConnector;

    public function __construct(PluginBase $plugin) {
        // 1. Inicjalizacja bazy danych (libasynql)
        $this->databaseConnector = libasynql::create($plugin, [
            "type" => "sqlite",
            "sqlite" => ["file" => "Database.db"]
        ], [
            "sqlite" => "queries.sql"
        ]);

        // 2. Inicjalizacja prostych managerów (nie mają zależności)
        $this->islandManager = new IslandManager($this->databaseConnector);
        $this->combatManager = new CombatManager();
        $this->regionManager = new RegionManager();
        $this->upgradeManager = new UpgradeManager();
        $this->economyManager = new EconomyManager();

        // 3. Inicjalizacja obiektów pomocniczych
        $config = new GeneratorConfig([]); // Tutaj przekazuję się wczytane dane

        // 4. Inicjalizacja GeneratorManagera (wstrzykujemy to, co zrobiliśmy powyżej)
        $this->generatorManager = new GeneratorManager(
            $this->islandManager,
            $this->upgradeManager,
            $config
        );

        // 5. Inicjalizacja głównego fasadowego managera
        $this->skyGenManager = new SkyGenManager(
            $this->islandManager,
            $this->generatorManager,
            $this->upgradeManager,
            $this->economyManager
        );
    }
}