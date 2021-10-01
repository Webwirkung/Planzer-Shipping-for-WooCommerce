<?php
/**
 * Plugin Name: WooCommerce Planzer
 * Description: Planzer WooCommerce Integration
 * Version: 0.0.1
 * Author: Webwirkung <info@webwirkung.ch>
 * Author URI: https://webwirkung.ch/
 * Text Domain: planzer
 */

define('PLANZER_NAME', 'Planzer');
define('PLANZER_ROOT_PATH', dirname(__FILE__));
define('PLANZER_ASSETS_PATH', dirname(__FILE__) . '/dist');
define('PLANZER_RESOURCES_PATH', dirname(__FILE__) . '/resources');
define('PLANZER_ASSETS_URI', home_url('/wp-content/plugins/woocommerce-planzer/dist'));
define('PLANZER_RESOURCES_URI', home_url('/wp-content/plugins/woocommerce-planzer/resources'));

register_activation_hook(__FILE__, function () {
    do_action('planzer/plugin/on_activate');
});

register_deactivation_hook(__FILE__, function () {
    do_action('planzer/plugin/on_deactivate');
});

require PLANZER_ROOT_PATH . '/inc/bootstrap.php';
