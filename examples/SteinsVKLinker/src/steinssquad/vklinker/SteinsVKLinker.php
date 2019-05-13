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

namespace steinssquad\vklinker;


use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use steinssquad\vklinker\handler\EventHandler;
use steinssquad\vklinker\provider\JsonProvider;
use steinssquad\vklinker\provider\Provider;


class SteinsVKLinker extends PluginBase implements Listener {

  private static $instance;

  public static function getInstance(): SteinsVKLinker {
    return self::$instance;
  }

  /** @var Provider */
  private $provider;

  public function onEnable() {
    self::$instance = $this;

    $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);

    $this->provider = new JsonProvider($this);
  }

  public function onDisable() {
    $this->provider->save();
  }

  public function getProvider(): Provider {
    return $this->provider;
  }
}