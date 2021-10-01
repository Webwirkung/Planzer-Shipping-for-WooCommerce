<?php

namespace Planzer\QRCode;

class QRCode
{
  public function getGeneratedQRUrl(string $data): string
  {
    $code_url = (new Generator())->getGeneratedQRUrl($data);
    Counter::increaseQRNumber();

    return $code_url;
  }
}
