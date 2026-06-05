<?php

namespace Core\Listeners;

use Core\Managers\CombatManager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

final readonly class CombatListener implements Listener {

    public function __construct(
        private CombatManager $combatManager
    ) {}

    public function onDamage(EntityDamageByEntityEvent $event) {
        $attacker = $event->getDamager();
        $victim = $event->getEntity();

        if ($attacker instanceof Player && $victim instanceof Player) {
            $this->combatManager->setCombatTag($attacker);
            $this->combatManager->setCombatTag($victim);
        }
    }

}