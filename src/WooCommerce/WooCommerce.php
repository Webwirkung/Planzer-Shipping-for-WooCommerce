<?php

namespace Planzer\WooCommerce;

use Planzer\WooCommerce\Services\ExclusionService;

class WooCommerce
{
    public function __construct()
    {
        $exclusionService = new ExclusionService();

        createClass('Planzer\\WooCommerce\\Submodules\\BulkAction', [$exclusionService]);
        createClass('Planzer\\WooCommerce\\Submodules\\OrderAction', [$exclusionService]);
        createClass('Planzer\\WooCommerce\\Submodules\\OrderStatus', [$exclusionService]);

        if ('yes' === get_option('planzer_sender_verify_address')) {
            createClass('Planzer\\WooCommerce\\Submodules\\CheckoutAddressValidation');
        }
    }
}