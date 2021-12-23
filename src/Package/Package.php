<?php

namespace Planzer\Package;

use Planzer\QRCode\Counter as QRCounter;
use Planzer\QRCode\QRCode;

class Package
{
  private const QR_PREFIX = 91;
  private const QR_SUFFIX = 8888888;
  private int $order_id;
  private int $sequence_number;
  private string $qr_url = '';

  public function __construct(int $order_id)
  {
    $this->order_id = $order_id;
    $this->sequence_number = QRCounter::getQRNumber();
    QRCounter::increaseQRNumber();
  }

  public function getPackageNumber(int $width = 20): string
  {
    return $this->sanitizeAndConvertToStr($this->order_id, $width);
  }

  public function getQRContent(): string
  {
    $suffix = $this->sanitizeAndConvertToStr(self::QR_SUFFIX, 20 + 30, ' '); // PLZ-59 sequence number must be shorter - the 30 space chars are added here
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

  public function getSequenceNumber(int $width = 9): string
  {
    return $this->sanitizeAndConvertToStr($this->sequence_number, $width);
  }

  /**
   * @param int $width - use 0 for not displaying any leading characters and keeping whole number
   */
  private function sanitizeAndConvertToStr(int $number, int $width, string $filler = '0'): string
  {
    if (0 >= strlen($filler)) {
      $filler = '0';
    } elseif (1 < strlen($filler)) {
      $filler = substr($filler, 0, 1);
    }
    $width = abs($width);

    return substr(sprintf('%' . $filler . $width . 'd', $number), -1 * $width);
  }

  public function getGeneratedQRUrl(): string
  {
    if (empty($this->qr_url)) {
      $this->qr_url = (new QRCode())->getGeneratedQRUrl($this->getQRContent());
    }

    return $this->qr_url;
  }
}
