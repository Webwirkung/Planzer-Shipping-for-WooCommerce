<?php

namespace Planzer\Helpers;

use WC_Order;

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

  public static function isShippingAddressFiled(WC_Order $order): bool
  {
    if ($order->get_billing_address_1() != $order->get_shipping_address_1()) {
      return true;
    }

    if ($order->get_shipping_city() != $order->get_billing_city()) {
      return true;
    }

    if ($order->get_shipping_postcode() != $order->get_billing_postcode()) {
      return true;
    }

    return false;
  }
}
