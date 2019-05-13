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

namespace steinssquad\vk\event\message;


use steinssquad\vk\api\attachment\Attachment;
use steinssquad\vk\api\VKApi;
use steinssquad\vk\event\VKEvent;


class MessageIncomingEvent extends VKEvent {

  private $date = 0;
  private $fromId = 0;
  private $id = 0;
  private $peerId = 0;
  private $text = "";
  private $conversationMessageId = 0;
  private $fwdMessages = [];
  private $important = false;
  private $randomId = 0;
  private $attachments = [];
  private $isHidden = false;

  public function __construct(array $eventData) {
    $this->date = $eventData['date'];
    $this->fromId = $eventData['from_id'];
    $this->id = $eventData['id'];
    $this->peerId = $eventData['peer_id'];
    $this->text = $eventData['text'];
    $this->conversationMessageId = $eventData['conversation_message_id'];
    $this->fwdMessages = $eventData['fwd_messages'];
    $this->important = $eventData['important'];
    $this->randomId = $eventData['random_id'];
    $this->attachments = array_filter(array_map(function (array $attachment) {
      return Attachment::fromArray($attachment);
    }, $eventData['attachments']));
    $this->isHidden = $eventData['is_hidden'];
  }

  public function getDate(): int {
    return $this->date;
  }

  public function getFromId(): int {
    return $this->fromId;
  }

  public function getId(): int {
    return $this->id;
  }

  public function getPeerId(): int {
    return $this->peerId;
  }

  public function hasText(): bool {
    return $this->text !== "";
  }

  public function getText(): string {
    return $this->text;
  }

  public function getConversationMessageId(): int {
    return $this->conversationMessageId;
  }

  public function getFwdMessages(): array {
    return $this->fwdMessages;
  }

  public function isImportant(): bool {
    return $this->important;
  }

  public function getRandomId(): int {
    return $this->randomId;
  }

  public function hasAttachments(?string $type = null) {
    return count($this->getAttachments($type)) > 0;
  }

  /**
   * @param string|null $type
   * @return Attachment[]
   */
  public function getAttachments(?string $type = null): array {
    return array_filter($this->attachments, function (Attachment $attachment) use ($type) {
      return $type === null || $attachment->getName() === $type;
    });
  }

  public function isHidden(): bool {
    return $this->isHidden;
  }


  public function sendMessage(string $message): string {
    return VKApi::sendMessage($this->peerId, [
      'message' => $message
    ]);
  }

  public function sendSticker(int $stickerID): string {
    return VKApi::sendMessage($this->peerId, [
      'sticker_id' => $stickerID,
    ]);
  }
}