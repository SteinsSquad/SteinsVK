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

namespace steinssquad\vklinker\handler;


use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use steinssquad\vk\api\VKApi;
use steinssquad\vk\event\message\MessageIncomingEvent;
use steinssquad\vklinker\SteinsVKLinker;


class EventHandler implements Listener {

  private $codes = [];

  /**
   * @param PlayerChatEvent $event
   * @priority HIGHEST
   * @ignoreCancelled true
   */
  public function handlePlayerChatEvent(PlayerChatEvent $event) {
    $player = $event->getPlayer();
    if (!(isset($this->codes[$player->getLowerCaseName()]))) return;
    if ($event->getMessage() !== $this->codes[$player->getLowerCaseName()][0]) return;
    $event->setCancelled();
    $player->sendMessage(TextFormat::GOLD . "[Привязка] " . TextFormat::WHITE . "Вы успешно привязали свой аккаунт к аккаунту вк.");
    $player->sendMessage(TextFormat::GOLD . "[Привязка] " . TextFormat::WHITE . "Чтобы отвзятать аккаунт, отправьте " . TextFormat::RED . "!отвязать" . TextFormat::WHITE . " нашему боту.");

    SteinsVKLinker::getInstance()->getProvider()->link($player->getName(), $this->codes[$player->getLowerCaseName()][1]);

    unset($this->codes[$player->getLowerCaseName()]);
  }

  public function handleMessageIncomingEvent(MessageIncomingEvent $event) {
    $message = $event->getText();
    $args = explode(" ", $message);
    if (($command = mb_strtolower(array_shift($args))) === '!привязать') {
      if (count($args) === 0) {
        $event->sendMessage('Пожалуйста, используйте !привязать <ник>');
        return false;
      }
      $playerName = array_shift($args);
      if (!Server::getInstance()->hasOfflinePlayerData($playerName) && Server::getInstance()->getPlayerExact($playerName) === null) {
        $event->sendMessage('К сожалению, такой игрок ещё не зарегестрирован на сервере.');
        return false;
      } else if (SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) !== null) {
        $event->sendMessage('К сожалению, ваш аккаунт уже привязан к нику ' . SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) . '!');
        return false;
      } else if (SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName) !== null) {
        $event->sendMessage("К сожалению, аккаунт [id" . SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName) . "|$playerName] уже привязан.");
        return false;
      }

      $attempts = 0;
      do {
        $code = substr(md5(mt_rand(0, PHP_INT_MAX)), 0, mt_rand(4, 6));
        if (++$attempts >= 5) {
          $event->sendMessage('В данный момент привязка невозможна. Попробуйте поздее.');
          return false;
        }
      } while (array_search($code, $this->codes) !== false);

      $event->sendMessage("Отлично! Первый шаг сделан. Теперь зайдите в игру и введите в чат $code.");
      $event->sendMessage("Внимание, данный код работает только до перезагрузки сервера, так что поторопитесь.");

      $this->codes[strtolower($playerName)] = [$code, $event->getFromId()];
    } else if ($command === '!отвязать') {
      if (SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) === null) {
        $event->sendMessage("К сожалению, ваш аккаунт не привязан к аккаунту в игре (!привязать).");
        return false;
      }
      SteinsVKLinker::getInstance()->getProvider()->unlink($event->getFromId());
      $event->sendMessage("Вы успешно отвязали свой аккаунт.");
    } else if ($command === '!инфа') {
      if (count($args) === 0) {
        $event->sendMessage('Пожалуйста, используйте !инфа <ник>');
        return false;
      }
      $playerName = array_shift($args);

      if (SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) === null) {
        $event->sendMessage("К сожалению, чтобы узнать аккаунт о другом игроке, вы должны привязать аккаунт (!привязать).");
        return false;
      }

      if (SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName) === null) {
        $event->sendMessage("К сожалению, аккаунт $playerName не привязан.");
        return false;
      }
      $lastPlayed = Server::getInstance()->getOfflinePlayer($playerName)->getLastPlayed();
      if ($lastPlayed !== null) {
        $lastPlayed = date("d-m-y H:i:s", intval($lastPlayed / 1000));
      }

      $event->sendMessage(
        "Аккаунт $playerName привязан на [id" . SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName) . "|эту страницу].\n" .
        "Игрок: " . (Server::getInstance()->getPlayerExact($playerName) === null ? "Оффлайн" : "Онлайн") . "\n" .
        "Последний раз был в игре: " . ($lastPlayed ?? "До вайпа") . "\n"
      );
    } else if ($command === '!баланс') {
      if (!class_exists("\\onebone\\economyapi\\EconomyAPI")) return false;

      if (SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) === null) {
        $event->sendMessage("К сожалению, вы должны привязать аккаунт (!привязать).");
        return false;
      }

      $event->sendMessage('Ваш баланс: $' . EconomyAPI::getInstance()->myMoney(SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId())));
    } else if ($command === '!передать') {
      if (!class_exists("\\onebone\\economyapi\\EconomyAPI")) return false;

      if (count($args) < 2) {
        $event->sendMessage('Пожалуйста, используйте !передать <ник> <кол-во>');
        return false;
      }

      if (SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId()) === null) {
        $event->sendMessage("К сожалению, вы должны привязать аккаунт (!привязать).");
        return false;
      }

      $playerName = array_shift($args);
      if (SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName) === null) {
        $event->sendMessage("К сожалению, аккаунт $playerName не зарегестрирован.");
        return false;
      }
      $amount = array_shift($args);
      if (!is_numeric($amount) || (int)$amount <= 0) {
        $event->sendMessage('Пожалуйста, используйте !передать <ник> <кол-во>');
        return false;
      }
      $amount = intval($amount);

      if (EconomyAPI::getInstance()->myMoney($player = SteinsVKLinker::getInstance()->getProvider()->getPlayerName($event->getFromId())) < $amount) {
        $event->sendMessage('У Вас нет столько на счету (!баланс).');
        return false;
      }
      EconomyAPI::getInstance()->reduceMoney($player, $amount);
      EconomyAPI::getInstance()->addMoney($playerName, $amount);

      $uID = SteinsVKLinker::getInstance()->getProvider()->getUserId($playerName);

      if ($uID !== null) {
        VKApi::sendMessage($uID, [
          'message' => "Игрок [id{$event->getFromId()}|$player] отправил вам \$$amount."
        ]);
        $playerName = "[id$uID|$playerName]";
      }
      $event->sendMessage("Вы успешно отправили \$$amount игроку $playerName.");
    }
    return true;
  }
}