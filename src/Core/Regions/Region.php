<?php

namespace Core\Regions;

use pocketmine\math\Vector3;

class Region {

    public function __construct(
        public readonly string $name,
        public readonly ?string $requiredRank, // null = spawn dla każdego
        public readonly Vector3 $min,
        public readonly Vector3 $max,
        public string $color = "white"
    ) {}

    public function isInside(Vector3 $pos): bool {
        return $pos->x >= $this->min->x && $pos->x <= $this->max->x &&
               $pos->y >= $this->min->y && $pos->y <= $this->max->y &&
               $pos->z >= $this->min->z && $pos->z <= $this->max->z;
    }
}