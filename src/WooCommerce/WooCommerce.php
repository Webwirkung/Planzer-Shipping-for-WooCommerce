<?php

namespace Planzer\WooCommerce;

class WooCommerce
{
  public function __construct()
  {
    createClass('Planzer\\WooCommerce\\Submodules\\OrderAction');
    createClass('Planzer\\WooCommerce\\Submodules\\CheckoutAddressValidation');
    createClass('Planzer\\WooCommerce\\Submodules\\OrderStatus');
  }
}
