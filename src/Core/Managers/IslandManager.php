<?php

namespace Core\Managers;

use Core\Island;
use pocketmine\math\Vector3;
use poggit\libasynql\DataConnector;

final class IslandManager {

    /** @var array<string, Island */
    private array $islands = [];

    public function __construct(
        private readonly DataConnector $databaseConnector
    ) {}

    // Metoda do rejestracji wyspy po wczytaniu z bazy
    public function loadIsland(Island $island): void {
        $this->islands[$island->getOwnerUuid()] = $island;
    }

    // Metoda do pobierania wyspy gracza
    public function getIslandByPlayer(string $uuid): ?Island {
        return $this->islands[$uuid] ?? null;
    }

    public function getIslandAt(Vector3 $pos): ?Island {
        foreach ($this->islands as $island) {
            $center = $island->getCenter();
            $radius = 32;

            if ($pos->x >= $center->x - $radius && $pos->x <= $center->x + $radius &&
                $pos->y >= $center->y - $radius && $pos->y <= $center->y + $radius &&
                $pos->z >= $center->z - $radius && $pos->z <= $center->z + $radius) {
                return $island;
            }
        }
        return null;
    }

    public function loadAllIslandsFromDatabase(?callable $onComplete = null): void {
        // 1. Pobieram dane z bazy
        $this->databaseConnector->executeSelect("skygen.load_islands", [], function(array $rows) use ($onComplete) {
            foreach ($rows as $row) {
                // Tworzę Vector3 dla środka wyspy
                $center = new Vector3((int)$row['x'], (int)$row['y'], (int)$row['z']);

                // Tworzę Vector3 dla generatora
                $genPos = new Vector3((int)$row['gen_x'], (int)$row['gen_y'], (int)$row['gen_z']);

                // Przekazuję oba do obiektu Island
                $this->islands[$row['ownerUuid']] = new Island(
                    $row['ownerUuid'],
                    (int) $row['level'],
                    $center,
                    $genPos
                );
            }

            if ($onComplete !== null) {
                $onComplete();
            }
        });
    }

    public function save(Island $island): void {
        $this->databaseConnector->executeChange("skygen.save_island", [
            "ownerUuid" => $island->getOwnerUuid(),
            "level" => $island->getLevel(),
            "x" => $island->getCenter()->getFloorX(),
            "y" => $island->getCenter()->getFloorY(),
            "z" => $island->getCenter()->getFloorZ()
        ]);
    }

    public function saveAll(): void {
        foreach ($this->islands as $island) {
            $this->save($island);
        }
    }

    public function getAllIslands(): array {
        return $this->islands;
    }

    public function getIslandByUuid(string $uuid): ?Island {
        return $this->islands[$uuid] ?? null;
    }
}