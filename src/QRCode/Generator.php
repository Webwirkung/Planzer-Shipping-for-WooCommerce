<?php

namespace Planzer\QRCode;

class Generator
{
  /**
   * @see https://goqr.me/api/doc/create-qr-code/
   */
  private string $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?';
  private string $qr_size = '182x182';
  private string $qr_margin = '0';
  private string $error_correction_code = 'L';

  public function __construct(string $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?', string $qr_size = '182x182', string $qr_margin = '0', string $error_correction_code = 'L')
  {
    $this->qr_url = $qr_url;
    $this->qr_size = $qr_size;
    $this->qr_margin = $qr_margin;
    $this->error_correction_code = $error_correction_code;
  }

  public function getGeneratedQRUrl(string $data): string
  {
    $code_url = add_query_arg(array_merge($this->getQRPrintOptions(), ['data' => urlencode($data)]), $this->qr_url);

    return $code_url;
  }

  private function getQRPrintOptions(): array
  {
    $options = [
      'size' => $this->qr_size,
      'margin' => $this->qr_margin,
      'ecc' => $this->error_correction_code,
    ];

    return apply_filters('planzer/qr/generator/print_options', $options);
  }
}
