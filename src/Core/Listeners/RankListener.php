<?php

namespace Core\Listeners;

use Core\Main;
use Core\Managers\RankManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\chat\StandardChatFormatter;
use pocketmine\scheduler\ClosureTask;

readonly class RankListener implements Listener {

    public function __construct(private RankManager $rankManager, private Main $plugin) {}

    public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $this->rankManager->ensureUserExists($player->getName());

        $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player): void {
            if ($player->isOnline()) {
                $this->rankManager->applyRank($player);
            }
        }),
            10
        );
    }

    public function onQuit(PlayerQuitEvent $event) : void {
        $this->rankManager->unregisterAttachment($event->getPlayer());
    }

    public function onChat(PlayerChatEvent $event) : void {
        $event->uncancel();

        $player = $event->getPlayer();
        $name = $player->getName();
        $message = $event->getMessage();

        $rank = (string)$this->plugin->getUsersConfig()->get($name, "Gracz");
        $prefix = (string)$this->plugin->getGroupsConfig()->getNested($rank .".prefix", "Gracz");

        $format = $prefix . " " . $name . " > " . $message;

        $event->setFormatter(new StandardChatFormatter($format));
    }
}