<?php

if (! function_exists('get_plugin_data') || ! function_exists('is_plugin_active')) {
  include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

function pluginRequirementsValid(): bool
{
  $css_class = 'notice notice-error';

  if (! phpRequirementsValid()) {
    add_action('admin_notices', function () use ($css_class) {
      printf('<div class="%1$s"><p><strong>PLANZER :</strong> %2$s</p></div>', esc_attr($css_class), esc_html(getPhpNoticeMessage()));
    });

    return false;
  }

  if (! wooCommerceRequirementsValid()) {
    add_action('admin_notices', function () use ($css_class) {
      printf('<div class="%1$s"><p><strong>PLANZER :</strong> %2$s</p></div>', esc_attr($css_class), esc_html(getWcNoticeMessage()));
    });

    return false;
  }

  return true;
}

function wooCommerceRequirementsValid(): bool
{
  if (! is_plugin_active(plugin_basename(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php'))) {
    return false;
  }

  $planzer_data = get_file_data(PLANZER_PLUGIN_FILE, ['RequiresWooCommerce' => 'WC requires at least'], 'plugin');
  $wc_data = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', false, /* $translate */ false);

  if (! empty($planzer_data['RequiresWooCommerce'])) {
    if (empty($wc_data['Version'])) {
      return false;
    }

    if (version_compare($wc_data['Version'], $planzer_data['RequiresWooCommerce'], '<')) {
      return false;
    }
  }

  return true;
}

function phpRequirementsValid(): bool
{
  $plugin_data = get_plugin_data(PLANZER_PLUGIN_FILE, false, /* $translate */ false);

  if (! empty($plugin_data['RequiresPHP'])) {
    if (version_compare(PHP_VERSION, $plugin_data['RequiresPHP'], '<')) {
      return false;
    }
  }

  return true;
}

function getPhpNoticeMessage(): string
{
  return __('Not sufficient PHP version, please update PHP to use plugin.', 'planzer');
}

function getWcNoticeMessage(): string
{
  return __('WooCommerce is not activated or has not sufficient version. Please enable/update WooCommerce to use plugin.', 'planzer');
}
