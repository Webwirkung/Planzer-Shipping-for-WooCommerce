<?php

namespace Planzer\CSV;

class CSVGroup
{
  private int $size = 1;
  private string $key_prefix = '';
  private array $data = [];

  public function __construct(int $size, string $key_prefix = '')
  {
    if (0 >= $size) {
      throw new \Exception("PLANZER ERROR: Group size has to be greater than 0!", 1);
    }
    $this->size = $size;
    $this->key_prefix = $key_prefix;
    for ($i = 0; $i < $this->size; $i++) {
      $this->data[$this->key_prefix . $i] = '';
    }
  }

  public function setField(int $position, string $value): bool
  {
    if (0 > $position || $this->size < $position) {
      if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $logger->error("PLANZER WARNING: Trying to access out of range group field: '$value' at position $position", ['source' => 'wc-planzer-shipping']);
      }

      return false;
    }

    $this->data[$position] = $value;
    return true;
  }

  public function getAsArray(): array
  {
    return $this->data;
  }
}
