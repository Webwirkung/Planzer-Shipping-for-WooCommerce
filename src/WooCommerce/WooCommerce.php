<?php

namespace Planzer\WooCommerce;

use Planzer\WooCommerce\Services\ExclusionService;
use Planzer\WooCommerce\Services\Transmitter;

class WooCommerce
{
    public function __construct()
    {
        $exclusionService = new ExclusionService();
        $transmitter = new Transmitter($exclusionService);

        createClass('Planzer\\WooCommerce\\Submodules\\BulkAction', [$exclusionService, $transmitter]);
        createClass('Planzer\\WooCommerce\\Submodules\\OrderAction', [$transmitter]);
        createClass('Planzer\\WooCommerce\\Submodules\\OrderStatus', [$transmitter]);

        if ('yes' === get_option('planzer_sender_verify_address')) {
            createClass('Planzer\\WooCommerce\\Submodules\\CheckoutAddressValidation');
        }
    }
}