<?php

namespace Planzer\WooCommerce;

class WooCommerce
{
  public function __construct()
  {
    createClass('Planzer\\WooCommerce\\Submodules\\OrderAction');
    createClass('Planzer\\WooCommerce\\Submodules\\OrderStatus');

    if ('yes' === get_option('planzer_sender_verify_address')) {
      createClass('Planzer\\WooCommerce\\Submodules\\CheckoutAddressValidation');
    }
  }
}
