<?php

/**
 * @file
 * Contains \Drupal\steam_vent\BotMeta.
 */

namespace Drupal\steam_vent;

use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;

/**
 * Bot wrapper.
 */
class BotMeta implements BotMetaInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * @var \Drupal\steam_vent\BotInterface
   */
  protected $bot;

  /**
   * Constructs a new BotMeta object.
   */
  public function __construct(ClientInterface $http_client, BotInterface $bot) {
    $this->httpClient = $http_client;
    $this->bot = $bot;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, BotInterface $entity) {
    return new static(
      $container->get('http_client'),
      $entity
    );
  }

  /**
   * {@inheritdoc}
   */
  function sendFriendCode(FriendCodeInterface $friend_code) {
    $this->httpClient->post($this->buildUrl('/friend_code'), [
      'json' => [
        'code' => $friend_code->getCode(),
        'user_label' => $friend_code->getUser()->label(),
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  function getNewClaims() {
    $codes = [];
    $result = $this->httpClient->get($this->buildUrl('/codes'));
    $friend_codes = Json::decode($result->getBody());

    if (is_array($friend_codes)) {
      foreach ($friend_codes as $friend_code) {
        if (!empty($friend_code['steamid'])) {
          $codes[$friend_code['code']] = $friend_code['steamid'];
        }
      }
    }

    return $codes;
  }

  /**
   * {@inheritdoc}
   */
  function purgeFriendCodes(array $friend_codes) {
    $codes = [];
    foreach ($friend_codes as $friend_code) {
      $codes[] = $friend_code->getCode();
    }

    $this->httpClient->post($this->buildUrl('/friend_code_purge'), [
      'json' => [
        'codes' => $codes,
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getFriendLimit() {
    return 250;
  }

  /**
   * {@inheritdoc}
   */
  public function countFriends() {
    // actual_friends + active friend codes for this bot.
  }

  /**
   * {@inheritdoc}
   */
  public function hasFreeFriendSlots() {
    return ($this->getFriendLimit() - $this->countFriends()) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage($steam_id, $message) {
    $this->httpClient->post($this->buildUrl('/send_message'), [
      'json' => [
        'steam_id' => $steam_id,
        'message' => $message,
      ],
    ]);
  }

  /**
   * Builds a REST URL.
   *
   * @param $suffix
   *   String to append.
   * @return string
   *   A URL.
   */
  protected function buildUrl($suffix) {
    return $this->bot->getUrl() . $suffix;
  }

}
