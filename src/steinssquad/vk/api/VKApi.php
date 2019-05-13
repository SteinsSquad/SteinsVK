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

namespace steinssquad\vk\api;


use pocketmine\Server;
use steinssquad\vk\task\AsyncRequestTask;


class VKApi {

  public const VERSION = "5.95";

  private static $accessToken = "";
  private static $groupID     = 0;

  public static function init(string $accessToken, int $groupID) {
    self::$accessToken = $accessToken;
    self::$groupID     = $groupID;
  }

  public static function sendMessage(int $peer_id, array $data): string {
    return self::curl('messages.send', array_merge([
      'peer_id' => $peer_id,
      'random_id' => mt_rand(0, 1000),
    ], $data));
  }

  public static function curl(string $method, array $params): string {
    $params['access_token'] = self::$accessToken;
    $params['v'] = self::VERSION;

    Server::getInstance()->getAsyncPool()->submitTask($task = new AsyncRequestTask($method, $params));

    return spl_object_hash($task);
  }

  public static function getAccessToken(): string {
    return self::$accessToken;
  }

  public static function getGroupId(): int {
    return self::$groupID;
  }
}