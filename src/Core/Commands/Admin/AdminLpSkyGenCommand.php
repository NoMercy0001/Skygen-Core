<?php

namespace Core\Commands;

use Core\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AdminLpSkyGenCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("lpcore", "Zarzadzanie rangami", "/lpcore", []);
        $this->setPermission("lpcore.admin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {

        if(!$this->testPermission($sender)) return;

        if (!isset($args[0])) {
            $sender->sendMessage("---- LPCORE INFO ----");
            $sender->sendMessage("/lpcore dodajgrupe <nazwa>");
            $sender->sendMessage("/lpcore ustawformat <nazwa> <format>");
            $sender->sendMessage("/lpcore dodajpermisje <nazwa> <permisja>");
            $sender->sendMessage("/lpcore usunpermisje <nazwa> <permisja>");
            $sender->sendMessage("/lpcore nadaj <gracz> <grupa>");
            return;
        }

        $groups = $this->plugin->getGroupsConfig();
        $users = $this->plugin->getUsersConfig();

        switch (strtolower($args[0])) {
            case "dodajgrupe":
                $name = $args[1] ?? "";
                if ($name === "" || $groups->exists($name)) {
                    $sender->sendMessage("Podaj poprawna/nowa nazwe grupy");
                    return;
                }

                $groups->set($name, ["prefix" => "[$name]", "permissions" => []]);
                $groups->save();
                $sender->sendMessage("Dodano grupe $name");
                break;

            case "ustawformat":
                if (count($args) < 3) {
                    $sender->sendMessage("Uzycie /lpcore ustawformat <nazwa> <format>");
                    return;
                }

                $group = $args[1];
                if (!$groups->exists($group)) return;
                array_shift($args); array_shift($args);
                $format = implode("", $args);
                $groups->setNested("$group.prefix", $format);
                $groups->save();
                $this->plugin->reloadAllPlayers();
                $sender->sendMessage("Zaktualizowano format grupy $group");
                break;

            case "dodajpermisje":
                if (count($args) < 3) return;
                $group = $args[1]; $perm = $args[2];
                if (!$groups->exists($group)) return;
                $perms = $groups->getNested("$group.permissions", []);
                if (!in_array($perm, $perms)) {
                    $perms[] = $perm;
                    $groups->setNested("$group.permissions", array_values($perms));
                    $groups->save();
                    $this->plugin->reloadAllPlayers();
                    $sender->sendMessage("Dodano permisje $perm do $group");
                }
                break;

            case "usunpermisje":
                if (count($args) < 3) return;
                $group = $args[1]; $perm = $args[2];
                $perms = $groups->getNested("$group.permissions", []);

                if (($key = array_search($perm, $perms)) !== false) {
                    unset($perms[$key]);
                    $groups->setNested("$group.permissions", array_values($perms));
                    $groups->save();
                    $this->plugin->reloadAllPlayers();
                    $sender->sendMessage("Usunieto permisje $perm z $group");
                }
                break;

            case "nadaj":
                if (count($args) < 3) return;
                $pName = $args[1]; $gName = $args[2];
                if (!$groups->exists($gName)) {
                    $sender->sendMessage("Ta ranga nie istnieje");
                    return;
                }
                $users->set($pName, $gName);
                $users->save();
                $target = $this->plugin->getServer()->getPlayerExact($pName);
                if ($target) $this->plugin->applyRank($target);
                $sender->sendMessage("Gracz $pName otrzymal range $gName");
                break;

            default:
                $sender->sendMessage("Nieznany Argument");
                break;
        }
    }
}