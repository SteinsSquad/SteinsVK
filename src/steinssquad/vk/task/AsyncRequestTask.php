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
use steinssquad\vk\SteinsVK;


class AsyncRequestTask extends AsyncTask {

  private $method;
  private $params;

  public function __construct(string $method, array $params) {
    $this->method = $method;
    $this->params = http_build_query($params);
  }

  public function onRun() {
    $this->setResult(Internet::getURL("https://api.vk.com/method/$this->method?$this->params"));
  }

  public function onCompletion(Server $server) {
    $sdk = $server->getPluginManager()->getPlugin('SteinsVK');
    if (!($sdk instanceof SteinsVK)) return;

    $sdk->callEvent([['type' => 'data_receive', 'object' => ['hash' => spl_object_hash($this), 'response' => json_decode($this->getResult(), true)]]]);
  }
}