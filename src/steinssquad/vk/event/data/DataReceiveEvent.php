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

namespace steinssquad\vk\event\data;


use steinssquad\vk\event\VKEvent;


class DataReceiveEvent extends VKEvent {

  private $hash = "";
  private $response = array();

  public function __construct(array $eventData) {
    $this->hash = $eventData['hash'];
    $this->response = $eventData['response'];
  }

  public function getHash(): string {
    return $this->hash;
  }

  public function getResponse(): ?array {
    return $this->response;
  }
}