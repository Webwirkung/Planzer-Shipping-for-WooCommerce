<?php

namespace Planzer\QRCode;

class Generator
{
  /**
   * @see https://developers.google.com/chart/infographics/docs/qr_codes
   */
  private string $qr_url = 'https://chart.googleapis.com/chart?cht=qr';
  private string $qr_size = '182x182';
  private string $qr_margin = '0';
  private string $error_correction_level = 'L';

  public function __construct(string $qr_url = 'https://chart.googleapis.com/chart?cht=qr', string $qr_size = '182x182', string $qr_margin = '0', string $error_correction_level = 'L')
  {
    $this->qr_url = $qr_url;
    $this->qr_size = $qr_size;
    $this->qr_margin = $qr_margin;
    $this->error_correction_level = $error_correction_level;
  }

  public function getGeneratedQRUrl(string $data): string
  {
    $code_url = add_query_arg(array_merge($this->getQRPrintOptions(), ['chl' => urlencode($data)]), $this->qr_url);
    return $code_url;
  }

  private function getQRPrintOptions(): array
  {
    $options = [
      'chs' => $this->qr_size,
      'chld' => $this->error_correction_level . '|' . $this->qr_margin,
    ];
    return apply_filters('planzer/qr/generator/print_options', $options);
  }
}
