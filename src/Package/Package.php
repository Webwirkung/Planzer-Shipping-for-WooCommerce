<?php

namespace Planzer\Package;

use Planzer\QRCode\Counter as QRCounter;

class Package
{
  private const QR_PREFIX = 91;
  private const QR_SUFFIX = 8888888;
  private int $order_id;

  public function __construct(int $order_id)
  {
    $this->order_id = $order_id;
  }

  public function getPackageNumber(): string
  {
    return sprintf('%020d', $this->order_id);
  }

  public function getQRContent(): string
  {
    $suffix = $this->sanitizeAndConvertToStr(self::QR_SUFFIX, 20, ' ');
    return $this->getQRContentWithoutSuffix() . $suffix;
  }

  public function getQRContentWithoutSuffix(): string
  {
    return self::QR_PREFIX . $this->getCustomerNumber() . $this->getControlNumber() . $this->getSequenceNumber();
  }

  private function getCustomerNumber(): string
  {
    return $this->sanitizeAndConvertToStr(get_option('planzer_sender_customer_number', 0), 6);
  }

  private function getControlNumber(): string
  {
    return $this->sanitizeAndConvertToStr(get_option('planzer_sender_control_number', 0), 3);
  }

  public function getSequenceNumber(): string
  {
    return $this->sanitizeAndConvertToStr(QRCounter::getQRNumber(), 39);
  }

  private function sanitizeAndConvertToStr(int $number, int $width, string $filler = '0'): string
  {
    if (0 >= strlen($filler)) {
      $filler = '0';
    } elseif (1 < strlen($filler)) {
      $filler = substr($filler, 0, 1);
    }

    return substr(sprintf('%' . $filler . $width . 'd', $number), -1 * $width);
  }
}
