<?php

/**
 * @file
 * Contains \Drupal\steam_vent\BotMetaInterface.
 */

namespace Drupal\steam_vent;

/**
 * Interface for bot wrapper.
 */
interface BotMetaInterface {

  /**
   *
   */
  function sendFriendCode(FriendCodeInterface $friend_code);

  function deleteFriendCode($friend_code, $bound = FALSE);

  /**
   *
   */
  function getNewClaims();

  /**
   * {@inheritdoc}
   */
  public function getFriendLimit();

  /**
   *
   */
  public function countFriends();

  /**
   *
   */
  public function hasFreeFriendSlots();

  /**
   *
   */
  public function sendMessage($steam_id, $message);
}
