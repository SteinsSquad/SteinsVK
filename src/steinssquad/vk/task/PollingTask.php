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

namespace steinssquad\vk\task;


use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use steinssquad\vk\api\VKApi;
use steinssquad\vk\SteinsVK;


class PollingTask extends AsyncTask {

  private $groupID;
  private $accessToken;

  public function __construct(string $accessToken, int $groupID) {
    $this->accessToken = $accessToken;
    $this->groupID     = $groupID;
  }

  private $server;
  private $key;
  private $ts;

  public function onRun() {
    $this->log("Запуск longpoll:");
    $this->initLongpoll();

    $this->log("Запуск сервиса:");
    while (!$this->hasCancelledRun()) {
      $response = Internet::getURL("$this->server?act=a_check&key=$this->key&ts=$this->ts&wait=10", 10);

      $json = json_decode($response, true);

      if (!is_array($json)) continue;


      if (isset($json['failed']) && $json['failed'] > 1) {
        $this->log("Ошибка #{$json['failed']}, переполучение ключа и временной метки:", TextFormat::RED . "Ошибка");
        $this->initLongpoll();
        continue;
      }
      $this->ts = $json['ts'];

      if (!isset($json['updates'])) continue;
      $this->publishProgress($json['updates']);
    }
  }


  public function onProgressUpdate(Server $server, $progress) {
    $sdk = $server->getPluginManager()->getPlugin('SteinsVK');
    if (!($sdk instanceof SteinsVK)) return;
    $sdk->callEvent($progress);
  }


  private function initLongpoll() {
    $response = Internet::getURL("https://api.vk.com/method/groups.getLongPollServer?group_id=$this->groupID&access_token=$this->accessToken&v=" . VKApi::VERSION);
    $json = json_decode($response, true);
    if (isset($json['error'])) {
      $this->log("Ошибка получения longpoll'а #{$json['error']['error_code']} : {$json['error']['error_msg']}", TextFormat::RED . "Ошибка");
      $this->log("Переподключение через 10 секунд.", TextFormat::RED . "Ошибка");
      $time = time() + 10;
      while (time() < $time) {
        continue;
      }
      $this->initLongpoll();
      return;
    }

    $this->key = $json['response']['key'];
    $this->server = $json['response']['server'];
    $this->ts = $json['response']['ts'];

    $this->log("Подключение к LongPoll успешное:");
    $this->log("Сервер: $this->server?key=$this->key");
    $this->log("Временная метка: $this->ts");
  }


  private function log(string $message, string $prefix = TextFormat::GREEN . "Информация") {
    echo Terminal::toANSI(TextFormat::GOLD . date("H-i-s") . TextFormat::BLUE . " [SteinsVK] " . TextFormat::GREEN . $prefix . TextFormat::GOLD . ": " . TextFormat::WHITE . $message . PHP_EOL);
  }
}