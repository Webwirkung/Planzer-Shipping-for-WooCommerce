<?php

namespace Planzer\QRCode;

class Counter
{
  public static function getQRNumber(): int
  {
    global $wpdb;

    $wpdb->query('START TRANSACTION');

    $currentSequence = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'planzer_qr_sequence_number' FOR UPDATE");

    $wpdb->query('COMMIT');

    return $currentSequence;
  }

  public static function getQRNumberAsString(): string
  {
    return sprintf(apply_filters('planzer/qr/counter/number_formatting', '%09d'), self::getQRNumber());
  }

  public static function increaseQRNumber(): bool
  {
    global $wpdb;

    $tableName = $wpdb->options;

    $wpdb->query('START TRANSACTION');

    $currentSequence = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'planzer_qr_sequence_number' FOR UPDATE");

    $newSequence = $currentSequence + 1;

    $dataUpdate = ['option_value' => $newSequence];
    $dataWhere = ['option_name' => 'planzer_qr_sequence_number'];

    $wpdb->update($tableName, $dataUpdate, $dataWhere);

    $wpdb->query('COMMIT');

    return $newSequence;
  }
}
