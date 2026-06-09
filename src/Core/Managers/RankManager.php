<?php

namespace Core\Managers;

use Core\Main;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;

class RankManager {

    /** @var PermissionAttachment[] */
    private array $attachments = [];

    public function __construct(private readonly Main $plugin) {}

    public function getPlayerRank(Player $player): string {
        return $this->plugin->getUsersConfig()->get($player->getName(), "Gracz");
    }

    public function ensureUserExists(string $playerName): void {
        if (!$this->plugin->getUsersConfig()->exists($playerName)) {
            $this->plugin->getUsersConfig()->set($playerName, "Gracz");
            $this->plugin->getUsersConfig()->save();
        }
    }

    public function applyRank(Player $player) : void {

        $name = $player->getName();
        $rank = $this->plugin->getUsersConfig()->get($name, "Gracz");

        $prefix = $this->plugin->getGroupsConfig()->getNested("$rank.prefix", "[Gracz]");

        $permissions = $this->plugin->getGroupsConfig()->getNested("$rank.permissions", []);

        $finalTag = $prefix ." ". $name;

        $player->setNameTag($finalTag);
        $player->setDisplayName($finalTag);

        $this->unregisterAttachment($player);

        $attachment = $player->addAttachment($this->plugin);

        $attachment->setPermission("pocketmine.group.user", true);
        $attachment->setPermission("pocketmine.broadcast.user", true);

        foreach ($permissions as $perm) {
            if ($perm === "*") {
                foreach (PermissionManager::getInstance()->getPermissions() as $permission) {
                    $attachment->setPermission($permission->getName(), true);
                }
            } else {
                $attachment->setPermission($perm, true);
            }
        }
        $this->attachments[$name] = $attachment;
    }

    public function unregisterAttachment(Player $player) : void {
        if (isset($this->attachments[$player->getName()])) {
            $player->removeAttachment($this->attachments[$player->getName()]);
            unset($this->attachments[$player->getName()]);
        }

        foreach ($player->getEffectivePermissions() as $perm) {
            $player->unsetBasePermission($perm->getPermission());
        }
    }

    public function reloadAllPlayers() : void {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $this->applyRank($onlinePlayer);
        }
    }
}