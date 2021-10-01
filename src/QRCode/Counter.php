<?php

namespace Planzer\QRCode;

class Counter
{
  private const OPTION_FIELD_NAME = 'planzer_qr_sequence_number';

  public static function getQRNumber(): int
  {
    return get_option(self::OPTION_FIELD_NAME, 1);
  }

  public static function getQRNumberAsString(): string
  {
    return sprintf(apply_filters('planzer/qr/counter/number_formatting', '%09d'), self::getQRNumber());
  }

  public static function increaseQRNumber(): bool
  {
    $increased_number = self::getQRNumber() + 1;
    return update_option(self::OPTION_FIELD_NAME, $increased_number);
  }
}
