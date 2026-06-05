<?php

namespace Core\Managers;

class EconomyManager {

    /** @var array<string, int> */
    private array $balances = [];

    public function getBalance(string $islandId): int {
        return $this->balances[$islandId] ?? 0;
    }

    public function addMoney(string $islandId, int $amount): void {
        $this->balances[$islandId] = $this->getBalance($islandId) + $amount;
    }

    public function subtractMoney(string $islandId, int $amount): bool {

        if ($this->getBalance($islandId) < $amount) return false;
        $this->balances[$islandId] -= $amount;
        return true;
    }
}