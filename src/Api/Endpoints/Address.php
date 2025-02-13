<?php

namespace Planzer\Api\Endpoints;

use Planzer\Helpers\Address as AddressHelper;

class Address extends AbstractEndpoint
{
  private array $requred_fields = [
    'shipping_postcode',
    'shipping_city',
    'shipping_country',
    'shipping_address_1'
  ];

  public function checkAddress(array $body): array
  {
    if (true === $this->areRequiredFieldsEmpty($body)) {
      return ['adressen' => []];
    }

    $parsed_address = AddressHelper::getParsedAddressLine($body['shipping_address_1']);

    $json_body = json_encode([
      'postCode' => $body['shipping_postcode'],
      'city' => $body['shipping_city'],
      'street' => $parsed_address['street'],
      'houseNumber' => $parsed_address['house_number'],
      'country' => $body['shipping_country']
    ]);

    try {
      $response = $this->callEndpoint('/adressen', $json_body, 'POST');
      return $response;
    } catch (\Throwable $th) {
      if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $logger->error('Planzer: ERROR with planzer API address validation', ['source' => 'wc-planzer-shipping']);
      }

      throw new \Exception(__('There was a problem with verifying your address, please try again later or contact administrator.', 'planzer'));
    }

    return [];
  }

  private function areRequiredFieldsEmpty(array $body): bool
  {
    foreach ($this->requred_fields as $field) {
      if (empty($body[$field])) {
        return true;
      }
    }

    return false;
  }
}
