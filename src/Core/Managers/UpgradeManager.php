<?php

namespace Core\Managers;

final class UpgradeManager {

    /** @var array<string, array<string, int>> */
    private array $upgrades = [];

    // Pobiera poziom konkretnego ulepszenia dla wyspy
    public function getUpgradeLevel(string $islandId, string $upgradeName): int {
        return $this->upgrades[$islandId][$upgradeName] ?? 0;
    }

    // Zwiększa poziom ulepszenia
    public function setUpgradeLevel(string $islandId, string $upgradeName, int $level): void {
        $this->upgrades[$islandId][$upgradeName] = $level;
    }
}