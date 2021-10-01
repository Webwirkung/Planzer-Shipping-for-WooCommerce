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
      $api_results = $api_address->checkAddress($data);

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
}
