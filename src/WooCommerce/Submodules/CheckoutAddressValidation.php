<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\Api\Models\ApiConfig;
use Planzer\Api\Endpoints\Address;
use WP_Error;

class CheckoutAddressValidation
{
  /**
   * @action woocommerce_after_checkout_validation
   *
   * @return void
   */
  public function checkoutAddressValidation(array $data, WP_Error $errors): void
  {
    if (! empty($errors->get_error_messages())) {
      return;
    }

    $api_address = new Address(new ApiConfig());

    try {
      $api_results = $api_address->checkAddress($this->sanitizeData($data));

      if (
          200 !== $api_results['http_code'] ||
          ! array_key_exists('adressen', $api_results) ||
          empty($api_results['adressen'])
      ) {
        $errors->add('validation', __('The address is invalid - please type in an existing address', 'planzer'), ['validator' => 'planzer-address-api']);
      }
    } catch (\Exception $e) {
      error_log('Planzer: ERROR with planzer API address validation');
      throw new \Exception(__('There was a problem with verifying your address, please try again later or contact administrator.', 'planzer'));
    }
  }

  private function sanitizeData(array $data): array
  {
    if (! empty($data['shipping_address_1'])) {
      $data['shipping_address_1'] = preg_replace(
          ['/St\.(?! )/', '/(\d) ([a-z]{1}|[A-Z]{1})$/'],
          ['St. ', '$1$2'],
          $data['shipping_address_1'],
      );
    }

    if (! empty($data['shipping_city'])) {
      $data['shipping_city'] = preg_replace(
          ['/St\.(?! )/'],
          ['St. '],
          $data['shipping_city'],
      );
    }
    return $data;
  }
}
