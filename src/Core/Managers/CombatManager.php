<?php

namespace Core\Managers;

use pocketmine\player\Player;

class CombatManager {

    /** @var int[] */
    private array $combatTags = [];
    private int $combatDuration = 30;

    public function setCombatTag(Player $player): void {
        $this->combatTags[$player->getName()] = time() + $this->combatDuration;
        $player->sendMessage("Jesteś podczas walki! Nie możesz wejść na bezpieczne strefy i spawn!");
    }

    public function isTagged(Player $player): bool {
        if (!isset($this->combatTags[$player->getName()])) {
            return false;
        }

        if (time() > $this->combatTags[$player->getName()]) {
            unset($this->combatTags[$player->getName()]);
            return false;
        }
        return true;
    }
}