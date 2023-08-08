<?php

/**
 * Plugin Name: Shipping via Planzer for WooCommerce
 * Description: Shipping via Planzer for WooCommerce
 * Version: 1.0.15
 * Author: Webwirkung <info@webwirkung.ch>
 * Author URI: https://webwirkung.ch/
 * Text Domain: planzer
 * Requires PHP: 7.4
 * Requires at least: 5.7
 * WC requires at least: 5.8
 * WC tested up to: 6.3
 */

define('PLANZER_NAME', 'Planzer');
define('PLANZER_ROOT_PATH', dirname(__FILE__));
define('PLANZER_PLUGIN_FILE', __FILE__);
define('PLANZER_ASSETS_PATH', dirname(__FILE__) . '/dist');
define('PLANZER_RESOURCES_PATH', dirname(__FILE__) . '/resources');
define('PLANZER_ASSETS_URI', plugin_dir_url(__FILE__) . 'dist');
define('PLANZER_RESOURCES_URI', plugin_dir_url(__FILE__) . 'resources');

register_activation_hook(__FILE__, function () {
    do_action('planzer/plugin/on_activate');
});

register_deactivation_hook(__FILE__, function () {
    do_action('planzer/plugin/on_deactivate');
});

require PLANZER_ROOT_PATH . '/inc/precheck.php';

if (! pluginRequirementsValid() && is_plugin_active(plugin_basename(__FILE__))) {
  deactivate_plugins(plugin_basename(__FILE__));
  if (isset($_GET['activate'])) {
    unset($_GET['activate']);
  }
} else {
  require PLANZER_ROOT_PATH . '/inc/bootstrap.php';
}
