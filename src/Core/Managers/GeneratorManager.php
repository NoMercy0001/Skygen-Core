<?php

namespace Core\Managers;

use Core\Generator;
use Core\GeneratorConfig;
use Core\Island;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use poggit\libasynql\DataConnector;

final class GeneratorManager {

    private array $generators = [];

    public function __construct(
        private readonly IslandManager $islandManager,
        private readonly UpgradeManager $upgradeManager,
        private readonly GeneratorConfig $config
    ) {}

    public function generateForIsland(Island $island, World $world, bool $isAllowed): void {
        // 1. Pobrałem poziom wydajności (opcjonalnie wpływający na drop)
        $level = $this->upgradeManager->getUpgradeLevel($island->getOwnerUuid(), "efficiency");

        // 2. Sprawdzam, czy blok w miejscu generowania jest powietrzem
        // Jeśli nie, nie generujemy nowego, żeby nie niszczyć surowców.
        if ($world->getBlock($island->getGeneratorPos())->getTypeId() !== VanillaBlocks::AIR()->getTypeId()) {
            return;
        }

        if (!$isAllowed) {
            $block = VanillaBlocks::STONE();
        } else {
            // 3. Losuje blok na podstawie konfiguracji
            $block = $this->getRandomBlockFromConfig();
        }

        // 4. Ustawiam blok w świecie w lokalizacji generatora
        $world->setBlock($island->getGeneratorPos(), $block);
    }

    private function getRandomBlockFromConfig(): Block {
        $drops = $this->config->getDrops(); // Tablica [ID => szansa]

        // Prosta logika losowania:
        $rand = mt_rand(1, 100);
        $current = 0;

        foreach ($drops as $block => $chance) {
            $current += $chance;
            if ($rand <= $current) {
                return $block; // Używam tego bo w config trzymam obiekt Block
            }
        }

        return VanillaBlocks::STONE();
    }

    public function getGeneratorConfig(): GeneratorConfig {
        return $this->config;
    }

    // 1. Dodawanie generatora do bazy i pamięci
    public function createGenerator(string $islandUuid, string $type, int $level, Vector3 $pos): void {
        $gen = new Generator($type, $level, $pos, $islandUuid);
        $this->generators[] = $gen;
    }

    // 2. Pobieranie generatorów dla wyspy
    public function getGeneratorByIsland(string $islandUuid): array {
        return array_filter($this->generators, fn(Generator $g) => $g->getIslandUuid() === $islandUuid);
    }

    // 3. Odświeżanie hologramu (FloatingText)
    public function updateHologram(Generator $generator): void {
        $text = "§7[§aGenerator§7]\n§9Przedmiot§f: §8" . ucfirst($generator->getType()) . "\n§fPoziom: §1" . str_repeat("⭐", $generator->getLevel());


    }

    // 4. Głowny Tick - serce generowania
    public function tickGenerators(World $world): void {

        /** @var Generator $generator */
        foreach ($this->generators as $generator) {
            $pos = $generator->getPosition();
            $block = $this->getBlockForType($generator->getType(), $generator->getLevel());

            // Stawianie bloku w świecie
            $world->setBlock($pos->add(0, 1, 0), $block);
        }

        /** @var IslandManager $islandManager */
        foreach ($this->islandManager->getAllIslands() as $allIsland) {
            // Logika generowania:
            $this->generateForIsland($allIsland, $world);
        }
    }

    public function getBlockForType(string $type, int $level): Block {
        return match ($type) {
            "coal" => $level >= 2 ? VanillaBlocks::COAL() : VanillaBlocks::COAL_ORE(),
            "iron" => VanillaBlocks::IRON(),
            "gold" => VanillaBlocks::GOLD(),
            "diamond" => VanillaBlocks::DIAMOND(),
            "netherite" => VanillaBlocks::NETHERITE(),
            default => VanillaBlocks::STONE()
            };
        }

        public function loadGenerators(DataConnector $dataConnector,IslandManager $islandManager): void {
            $dataConnector->executeSelect("skygen.generators.load_all", [], function (array $rows) use ($islandManager) {
                foreach ($rows as $row) {
                    $island = $islandManager->getIslandByUuid($row["island_uuid"]);
                    if ($island !== null) {
                        $gen = new Generator(
                          $row["type"],
                          (int)$row["level"],
                          new Vector3($row["x"], $row["y"], $row["z"]),
                          $row["island_uuid"],
                        );
                        $this->generators[] = $gen;
                        $island->addGenerator($gen);
                    }
                }
            });
        }
    }