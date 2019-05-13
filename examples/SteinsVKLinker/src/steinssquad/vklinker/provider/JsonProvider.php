<?php
/**
 *
 *    _     _           _    _
 *   | \   / |         | |  | |
 *   |  \_/  | ___  ___| |__| |
 *   |       |/ _ \/ __| ___| |
 *   | |\_/| |  __/\__ \ |_ | |
 *   |_|   |_|\___||___/___| \_\
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Mestl <mestl.dev@gmail.com>
 * @link   https://vk.com/themestl
 */

namespace steinssquad\vklinker\provider;


use pocketmine\utils\Config;
use steinssquad\vklinker\SteinsVKLinker;


class JsonProvider implements Provider {

  private $config;

  public function __construct(SteinsVKLinker $plugin) {
    $this->config = new Config($plugin->getDataFolder() . "users.json", Config::JSON);
  }

  public function getUserId(string $playerName): ?int {
    return $this->config->get(strtolower($playerName), null);
  }

  public function getPlayerName(int $userId): ?string {
    return array_search($userId, $this->config->getAll()) ?: null;
  }

  public function link(string $playerName, int $userId): bool {
    if ($this->getUserId($playerName) === null && $this->getPlayerName($userId) === null) {
      $this->config->set(strtolower($playerName), $userId);
      return true;
    }
    return false;
  }

  public function unlink(int $userId): bool {
    if ($this->getPlayerName($userId) !== null) {
      $this->config->remove($this->getPlayerName($userId));
      return true;
    }
    return false;
  }

  public function save() {
    $this->config->save();
  }
}