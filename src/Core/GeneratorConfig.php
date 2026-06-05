<?php

namespace Core;

final class GeneratorConfig {

    /**
     * @param array<int, int> $drops (np. [ID_BLOKU => SZANSA])
     */
    public function __construct(
        private readonly array $drops
    ) {}

    public function getDrops(): array {
        return $this->drops;
    }
}