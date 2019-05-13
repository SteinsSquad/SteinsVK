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

namespace steinssquad\vkstatus;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use steinssquad\vk\api\VKApi;


class SteinsVKStatus extends PluginBase implements Listener {

  private $joinTimes   = 0;
  private $quitTimes   = 0;
  private $uniqueJoins = 0;
  private $kicksTimes  = 0;
  private $chatUses    = 0;
  private $commandUses = 0;
  private $breakTimes  = 0;
  private $placeTimes  = 0;
  private $deathTimes  = 0;
  private $killTimes   = 0;
  private $movedBlocks = 0;

  public function onEnable() {
    $this->saveDefaultConfig();

    $this->getServer()->getPluginManager()->registerEvents($this, $this);

    VKApi::sendMessage($this->getConfig()->get('peer_id'), ['message' => '* Сервер включился.']);

    $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function(int $currentTick): void {
      VKApi::sendMessage($this->getConfig()->get('peer_id'), [
        'message' =>
          "* Статистика сервера на промежуток с " . date('H:i:s', time() - 60 * $this->getConfig()->get('interval')) . " по " . date("H:i:s") . " " . count(Server::getInstance()->getOnlinePlayers()) . "/" . Server::getInstance()->getMaxPlayers() . ":\n" .
          " - Входов: $this->joinTimes ($this->uniqueJoins новых)\n" .
          " - Выходов: $this->quitTimes ($this->kicksTimes киков)\n" .
          " - Сообщений отправлено: $this->chatUses ($this->commandUses комманд)\n" .
          " - Блоков: $this->breakTimes сломано, $this->placeTimes поставлено.\n" .
          " - Смертей: $this->deathTimes ($this->killTimes убийств).\n" .
          " - Игроки прошли $this->movedBlocks блоков.\n" .
          " - Статус сервера: " . Server::getInstance()->getTicksPerSecond() . " тпс."
      ]);
      $this->joinTimes = $this->quitTimes = $this->uniqueJoins = $this->kicksTimes = $this->chatUses = $this->commandUses = $this->breakTimes  = $this->placeTimes = $this->deathTimes = $this->killTimes = $this->movedBlocks = 0;
    }), $time = 20 * 60 * $this->getConfig()->get('interval'), $time);
  }

  public function onDisable() {
    //TODO: это может срабатывать не только при выключении сервера.
    VKApi::sendMessage($this->getConfig()->get('peer_id'), [
      'message' => "* Сервер был отключен."
    ]);
  }

  /**
   * @param PlayerJoinEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerJoinEvent(PlayerJoinEvent $event) {
    $this->joinTimes++;
    if (!$event->getPlayer()->hasPlayedBefore()) {
      $this->uniqueJoins++;
    }
  }

  /**
   * @param PlayerQuitEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerQuitEvent(PlayerQuitEvent $event) {
    $this->quitTimes++;
  }

  /**
   * @param PlayerKickEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerKickEvent(PlayerKickEvent $event) {
    $this->quitTimes++;
    $this->kicksTimes++;
  }

  /**
   * @param PlayerCommandPreprocessEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerChatEvent(PlayerCommandPreprocessEvent $event) {
    $this->chatUses++;
    if (substr($event->getMessage(), 0, 1) === '/' || substr($event->getMessage(), 0, 2) === './') {
      $this->commandUses++;
    }
  }

  /**
   * @param BlockBreakEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handleBlockBreakEvent(BlockBreakEvent $event) {
    $this->breakTimes++;
  }

  /**
   * @param BlockPlaceEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handleBlockPlaceEvent(BlockPlaceEvent $event) {
    $this->placeTimes++;
  }

  /**
   * @param PlayerDeathEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerDeathEvent(PlayerDeathEvent $event) {
    $this->deathTimes++;
    $cause = $event->getPlayer()->getLastDamageCause();
    if ($cause instanceof EntityDamageByEntityEvent && $cause->getDamager() instanceof Player) {
      $this->killTimes++;
    }
  }

  /**
   * @param PlayerMoveEvent $event
   * @priority MONITOR
   * @ignoreCancelled true
   */
  public function handlePlayerMoveEvent(PlayerMoveEvent $event) {
    $this->movedBlocks += $event->getTo()->distance($event->getFrom());
  }
}