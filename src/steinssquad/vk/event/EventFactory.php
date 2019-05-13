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

namespace steinssquad\vk\event;


use steinssquad\vk\event\data\DataReceiveEvent;
use steinssquad\vk\event\message\MessageIncomingEvent;


class EventFactory {

  /** @var VKEvent[] */
  private static $events = [];

  public static function init() {
    self::$events['message_new'] = MessageIncomingEvent::class;
    self::$events['data_receive'] = DataReceiveEvent::class;

  }

  public static function getEvent(array $event): ?VKEvent {
    if (isset(self::$events[$event['type']])) {
      return new self::$events[$event['type']]($event['object']);
    } else if (isset($event['type'])) {
      echo "Незаимплеменченное событие: {$event['type']}" . PHP_EOL;
    }
    return null;
  }
}