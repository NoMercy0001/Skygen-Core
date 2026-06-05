<?php

namespace Core;

use pocketmine\math\Vector3;

final class Island {

    /**
     * @param string $ownerUuid UUID właściciela wyspy
     * @param int $level Poziom wyspy
     * @param Vector3 $center Punkt środkowy wyspy
     */

    private Vector3 $generatorPos;

    /** @var Generator[] */
    private array $generators = [];

    public function __construct(
        private readonly string $ownerUuid,
        private int $level,
        private readonly Vector3 $center,
        Vector3 $generatorPos
    ) {
        $this->generatorPos = $generatorPos;
    }

    public function getGeneratorPos(): Vector3 {
        return $this->generatorPos;
    }

    public function setGeneratorPos(Vector3 $pos): void {
        $this->generatorPos = $pos;
    }

    public function getOwnerUuid(): string {
        return $this->ownerUuid;
    }

    public function getLevel(): int {
        return $this->level;
    }

    public function setLevel(int $level): void {
        $this->level = $level;
    }

    public function getCenter(): Vector3 {
        return $this->center;
    }

    public function addGenerator(Generator $generator): void {
        $this->generators[] = $generator;
    }

    /**
     * @return Generator[]
     */
    public function getGenerators(): array {
        return $this->generators;
    }

    public function removeGenerator(Generator $generator): void {
        foreach ($this->generators as $key => $gen) {
            if ($gen === $generator) {
                unset($this->generators[$key]);
                break;
            }
        }
    }
}