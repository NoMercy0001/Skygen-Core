<?php

namespace Core\Listeners;

use Core\Managers\RegionManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class RegionListener implements Listener {

    public function __construct(
        private RegionManager $regionManager
    ) {}

    public function onMove(PlayerMoveEvent $event): void {
        $to = $event->getTo();
        $region = $this->regionManager->getRegionAt($to);

        if ($region !== null && $region->requiredRank !== null) {
            if (!$this->playerHasRank($event->getPlayer(), $region->requiredRank)) {
                $event->getPlayer()->sendTitle("");
                $event->cancel();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $region = $this->regionManager->getRegionAt($event->getBlock()->getPosition());
        if ($region !== null && !$event->getPlayer()->hasPermission("admin.bypass")) {
            $event->cancel();
        }
    }
}