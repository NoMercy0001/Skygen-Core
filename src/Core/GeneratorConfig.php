<?php

namespace Core;

use pocketmine\utils\Config;

final readonly class GeneratorConfig {

    private Config $config;
    private array $drops;

    public function __construct(
        Main $plugin
    ) {
        $this->config = new Config($plugin->getDataFolder() . "config.yml", Config::YAML);
        $this->drops = $this->config->getNested("generator.blocks", []);
    }

    public function getDrops(): array {
        return $this->drops;
    }

    public function getBlockChance(int $id): float {
        return (float) $this->config->getNested("generator.blocks.$id.chance", 0.0);
    }

    public function getRequiredRank(string $regionName): string {
        return (string) $this->config->getNested("regions.$regionName.required_rank", "Gracz");
    }

    public function canEnter(string $playerRank, string $regionName): bool {
        $ranks = $this->config->get("ranks", ["Gracz", "Vip", "Svip", "Sponsor", "Swagger", "Elite", "Admin"]);

        $requiredRank = $this->getRequiredRank($regionName); // np. Vip

        // Znajdż indexy rang
        $playerRankIndex = array_search($playerRank);
        $requiredRankIndex = array_search($requiredRank, $ranks);

        // Jeśli gracz ma rangę wyższą lub równą wymaganej (np. Admin ma index 6 lub 7, a Vip ma 1 lub 2).
        return $playerRankIndex >= $requiredRankIndex;
    }
}