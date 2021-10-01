<?php

namespace Planzer\CSV;

use Carbon\Carbon;
use WC_Order;

class CSV
{
  private ?WC_Order $order = null;
  private ?DataLoader $data = null;

  public function __construct(WC_Order $order)
  {
    $this->order = $order;
    $this->data = new DataLoader($this->order);
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
      error_log('PLANZER WARNING: CSV group needs to have name and non zero size');
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
