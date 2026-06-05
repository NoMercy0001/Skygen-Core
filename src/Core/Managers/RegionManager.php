<?php

namespace Core\Managers;

use Core\Regions\Region;
use pocketmine\math\Vector3;
use poggit\libasynql\DataConnector;

class RegionManager {

    /** @var Region[] */
    private array $regions = [];

    private DataConnector $db;

    public function loadRegionsFromDatabase(): void {
        $this->db->executeSelect("skygen.region.load_all", [], function (array $rows) {
            foreach ($rows as $row) {
                $region = new Region(
                    $row['name'],
                    $row['required_rank'],
                    new Vector3((int)$row['min_x'], (int)$row['min_y'], (int)$row['min_z']),
                    new Vector3((int)$row['max_x'], (int)$row['max_y'], (int)$row['max_z'])
                );

                $this->regions[$row['name']] = $region;
            }
        });
    }

    public function addRegion(Region $region): void {
        $this->regions[$region->name] = $region;

        $this->db->executeInsert("skygen.regions.save", [
            "name" => $region->name,
            "required_rank" => $region->requiredRank,
            "min_x" => $region->min->x, "min_y" => $region->min->y, "min_z" => $region->min->z,
            "max_x" => $region->max->x, "max_y" => $region->max->y, "min_z" => $region->max->z
        ]);
    }

    public function getRegionAt(Vector3 $pos): ?Region {
        foreach ($this->regions as $region) {
            if ($region->isInside($pos)) return $region;
        }
        return null;
    }
}