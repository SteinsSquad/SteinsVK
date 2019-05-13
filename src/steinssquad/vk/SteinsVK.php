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

namespace steinssquad\vk;


use pocketmine\plugin\PluginBase;
use steinssquad\vk\api\VKApi;
use steinssquad\vk\event\EventFactory;
use steinssquad\vk\task\PollingTask;


class SteinsVK extends PluginBase {

  public const PROD_BUILD = false;

  /** @var PollingTask */
  private $task;

  public function onLoad() {
    $this->saveDefaultConfig();
    if ($this->getConfig()->get('access_token') === '') {
      $this->getLogger()->notice("Вы не настроили данный плагин.");
      $this->getLogger()->notice("Инструкция по установке: https://vk.cc/9nZAx7");
      return;
    }
    EventFactory::init();
    VKApi::init($this->getConfig()->get('access_token'), $this->getConfig()->get('group_id'));
    $this->getServer()->getAsyncPool()->submitTask($this->task = new PollingTask($this->getConfig()->get('access_token'), $this->getConfig()->get('group_id')));
  }

  public function onDisable() {
    $this->task->cancelRun();
  }

  public function callEvent(array $updates) {
    foreach ($updates as $event) {
      $event = EventFactory::getEvent($event);
      if (is_null($event)) continue;

      $event->call();
    }
  }
}