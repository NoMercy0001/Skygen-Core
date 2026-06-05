<?php

namespace Core;

use pocketmine\math\Vector3;

class Generator {

    public function __construct(
        private string $type, // np. coal, iron, gold, emerald, diamond, netherite
        private int $level, // poziom generatora (gwiazdki)
        private Vector3 $position, // gdzie w świecie stoi generator
        private string $islandUuid // do której wyspy należy
    ) {}

    // Gettery
    public function getType(): string { return $this->type; }
    public function getLevel(): int { return $this->level; }
    public function getPosition(): Vector3 { return $this->position; }
    public function getIslandUuid(): string { return $this->islandUuid; }

    // Settery (potrzebne do updatów , np. podnoszenie poziomów
    public function setLevel(int $level): void { $this->level = $level; }
    public function setType(string $type): void { $this->type = $type; }
}