<?php

namespace Planzer\Helpers;

class Address
{
  public static function getParsedAddressLine(string $address): array
  {
    $parts = explode(' ', $address);
    $house_number = end($parts);
    $has_numbers = false;

    for ($i = 0; $i <= strlen($house_number) - 1; $i++) {
      if (is_numeric($house_number[$i])) {
        $has_numbers = true;
        break;
      }
    }

    if ($has_numbers) {
      unset($parts[array_key_last($parts)]);
      return [
        'street' => implode(' ', $parts),
        'house_number' => $house_number,
      ];
    }

    return [
      'street' => $address,
      'house_number' => '',
    ];
  }
}
