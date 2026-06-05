<?php

namespace Core\Listeners;

use Core\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\chat\StandardChatFormatter;
use pocketmine\scheduler\ClosureTask;

class RankListener implements Listener {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $users = $this->plugin->getUsersConfig();

        if (!$users->exists($player->getName())) {
            $users->set($player->getName(),"Gracz");
            $users->save();
        }

        $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player): void {
            if ($player->isOnline()) {
                $this->plugin->applyRank($player);
            }
        }),
            10
        );
    }

    public function onQuit(PlayerQuitEvent $event) : void {
        $this->plugin->unregisterAttachment($event->getPlayer());
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