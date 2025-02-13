<?php

namespace Planzer\CSV;

use Planzer\Package\Package;
use WC_Order;

class CSV
{
  private ?WC_Order $order = null;
  private ?DataLoader $data = null;
  private Package $package;

  public function __construct(WC_Order $order, Package &$package)
  {
    $this->order = $order;
    $this->package = $package;
    $this->data = new DataLoader($this->order, $this->package);
  }

  public function generateGroupA(): string
  {
    return $this->generateGroup('A', 87);
  }

  public function generateGroupP(): string
  {
    return $this->generateGroup('P', 9);
  }

  public function generateGroupO(): string
  {
    return $this->generateGroup('O', 2);
  }

  private function generateGroup(string $name, int $size): ?string
  {
    if (empty($name) || empty($size)) {
      if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $logger->error('PLANZER WARNING: CSV group needs to have name and non zero size', ['source' => 'wc-planzer-shipping']);
      }

      return null;
    }

    $group = new CSVGroup($size);

    for ($i = 0; $i < $size; $i++) {
      $group->setField($i, $this->data->getGroupFieldData($name, $i));
    }

    return implode(';', $group->getAsArray());
  }

  public function getCsvContent(): string
  {
    return apply_filters(
        'planzer/csv/content',
        mb_convert_encoding(
            $this->generateGroupA() . "\r\n" .
            $this->generateGroupP() . "\r\n" .
            $this->generateGroupO(),
            'UTF-8'
        ),
        $this->order
    );
  }
}
