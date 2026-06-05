<?php

namespace Commands\Admin;

use Core\Listeners\RegionListener;
use Core\Main;
use Core\Managers\RegionManager;
use Core\Regions\Region;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class AdminRegionSkyGenCommand extends Command {

    public function __construct(private Main $plugin, private RegionListener $regionListener, private RegionManager $regionManager) {
        parent::__construct("region", "Zarządzanie regionami");
        $this->setPermission("admin.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($this->testPermission($sender)) return;

        switch ($args[0] ?? "") {
            case "wand":
            if ($sender instanceof Player) {
                $axe = VanillaItems::WOODEN_AXE()->setCustomName("Różdżka Regionów");
                $axe->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 10));
                $sender->getInventory()->addItem($axe);
                $sender->sendMessage("Otrzymałeś różdżkę! Left Click - P1, Right Click - P2.");
            } else {
                $sender->sendMessage("Tylko gracz może otrzymać różdżkę.");
            }
            break;
            case "add":
                if (count($args) < 2) return;
                $p1 = $this->regionListener->getPos1($sender->getName());
                $p2 = $this->regionListener->getPos2($sender->getName());
                if (!$p1 || !$p2 ) { $sender->sendMessage("Najpierw zaznacz P1 i P2!"); return; }
                $reg = new Region($args[1], $args[2] ?? "Gracz", $p1, $p2);
                $this->regionManager->addRegion($reg);
                $sender->sendMessage("Region " . $args[1] . " utworzony!");
                break;
            case "delete":
                $this->regionManager->removeRegion($args[1]);
                $sender->sendMessage("Region usunięty.");
                break;
            case "list":
                $regions = $this->regionManager->getAllRegions();
                $sender->sendMessage("--- Aktywne Regiony ---");
                if (empty($regions)) {
                    $sender->sendMessage("Brak zdefiniowanych regionów!");
                } else {
                    foreach ($regions as $name => $region) {
                        $sender->sendMessage("- $name (Wymaga: {$region->requiredRank})");
                    }
                }
                break;
            default:
                $sender->sendMessage("Użycie: /region <wand|add|delete|list>");
        }
    }

}