<?php

namespace Planzer\Note\Abstracts;

use Mpdf\Mpdf;
use WC_Order;
use Planzer\Package\Package;

abstract class Note
{
  protected Mpdf $mpdf;

  protected string $body = '';

  protected WC_Order $order;

  protected Package $package;

  public function __construct(WC_Order $order, Package &$package, $note_format = 'A4')
  {
    $this->mpdf = new Mpdf(['format' => $note_format]);
    $this->mpdf->defaultfooterline = 0;
    $this->order = $order;
    $this->package = $package;
  }

  public function getMpdf(): Mpdf
  {
    return $this->mpdf;
  }

  public function addToBody(string $text): self
  {
    $this->body .= $text;

    return $this;
  }

  public function getBody(): string
  {
    return $this->body;
  }

  protected function getFullCountryName(string $country_code): string
  {
    $wc_countries = WC()->countries->countries;

    if (array_key_exists($country_code, $wc_countries)) {
      $country = $wc_countries[$country_code];
    } else {
      $country = $country_code;
    }

    return $country;
  }

  public function getReferenceNumber(): string
  {
    return "{$this->order->get_id()}_{$this->package->getSequenceNumber(0)}";
  }
}
