<?php

namespace Core\Listeners;

use Core\Main;
use Core\Managers\CombatManager;
use Core\Managers\RegionManager;
use Core\Regions\Region;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;

class RegionListener implements Listener {

    public array $pos1 = [];
    public array $pos2 = [];

    public function __construct(
        private readonly Main $plugin,
        private readonly RegionManager $regionManager,
        private readonly CombatManager $combatManager
    ) {}

    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $to = $event->getTo();
        $region = $this->regionManager->getRegionAt($to);

        if ($region !== null) {
            $isTagged = $this->combatManager->isTagged($player);

            if ($isTagged && $region->name === "Spawn") {
                $event->cancel();
                $player->sendTip("Nie możesz wejść na spawn podczas walki!");
                $this->showGlassBarrier($player, $region);
                return;
            }
        }

        if ($region !== null && $region->requiredRank !== null) {
            if (!$this->hasRequireRank($player, $region->requiredRank)) {
                $this->showGlassBarrier($player, $region);
                $event->getPlayer()->sendTitle("");
                $event->cancel();
            } else {
                $this->sendRegionEffect($player, $region);
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $region = $this->regionManager->getRegionAt($event->getBlock()->getPosition());
        if ($region !== null && !$event->getPlayer()->hasPermission("admin.bypass")) {
            $event->cancel();
        }
    }

    private function hasRequireRank(Player $player, string $requiredRank): bool {
        $playerRank = $this->plugin->getUsersConfig()->get($player->getName(), "Gracz");

        if ($playerRank === "Admin") return true;

        return $playerRank === $requiredRank;
    }

    public function setPos1(string $name, Vector3 $pos) { $this->pos1[$name] = $pos; }
    public function setPos2(string $name, Vector3 $pos) { $this->pos2[$name] = $pos; }
    public function getPos1(string $name): ?Vector3 { return $this->pos1[$name] ?? null; }
    public function getPos2(string $name): ?Vector3 { return $this->pos2[$name] ?? null; }

    public function onInteract(PlayerInteractEvent $event): void {
        $item = $event->getItem();
        if ($item->getCustomName() !== "Różdżka Regionów") return;

        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPosition();

        if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            $this->setPos1($player->getName(), $pos);
            $player->sendPopup("P1 ustawione na " . $pos->getX() . ", " . $pos->getY());
            $event->cancel();
        } elseif ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            $this->setPos2($player->getName(), $pos);
            $player->sendPopup("P2 ustawione na " . $pos->getX() . ", " . $pos->getY());
            $event->cancel();
        }
    }

    public function onBreakBlocks(BlockBreakEvent $breakEvent): void {
        if ($breakEvent->getItem()->getCustomName() === "Różdżka Regionów") {
            $breakEvent->cancel();
        }
    }

    public function showGlassBarrier(Player $player, Region $region): void {
        $glass = match ($region->color) {
            "purple" => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::PURPLE), // Elite
            "cyan" => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::CYAN), // Swagger
            "orange" => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE), // Sponsor
            "yellow" => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::YELLOW), // SVip
            "green" => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN), // Vip
            default => VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::WHITE) // Spawn or Lobby or Hub
        };

        $pk = UpdateBlockPacket::create(BlockPosition::fromVector3($region->min), $glass->getStateId(), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::FLAG_NETWORK);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    private function sendRegionEffect(Player $player, Region $region): void {
        $player->sendTip("Witaj w strefie: " . $region->name);
    }
}