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

namespace steinssquad\vk\api\attachment;


class PhotoAttachment extends Attachment {

  public const SMALL_SIZES = ['m', 's'];
  public const MEDIUM_SIZES = ['y', 'r', 'q', 'p', 'm', 's'];
  public const LARGE_SIZES = ['w', 'z', 'y', 'r', 'q', 'p', 'm', 's'];

  private $id = 0;
  private $ownerId = 0;
  private $date = 0;
  private $accessKey = '';
  private $urls = [];

  public function __construct(array $data) {
    $this->id = $data['id'];
    $this->ownerId = $data['owner_id'];
    $this->date = $data['date'];
    $this->accessKey = $data['access_key'] ?? null;
    $this->urls = $data['sizes'];
  }

  public function getId(): int {
    return $this->id;
  }

  public function getOwnerId(): int {
    return $this->ownerId;
  }

  public function getDate(): int {
    return $this->date;
  }

  public function getAccessKey(): ?string {
    return $this->accessKey;
  }

  public function getURL(array $sizes = self::LARGE_SIZES): string {
    $found = [];
    foreach ($sizes as $size) {
      $found = array_map(function(array $urls) {return $urls['url'];}, array_filter($this->urls, function (array $urls) use ($size) {
        return $urls['type'] === $size;
      }));
      if (count($found) > 0) break;
    }
    return array_shift($found);
  }

  public function getName(): string {
    return 'photo';
  }
}