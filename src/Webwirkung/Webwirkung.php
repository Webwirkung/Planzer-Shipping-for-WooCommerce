<?php

namespace Planzer\Webwirkung;

use Planzer\QRCode\Counter;

class Webwirkung
{
  private const HEARTBEAT_URL = 'https://plugin.webwirkung.dev/api/v1/customers/heartbeat';

  /**
   * @action init
   */
  public function scheduleEvents(): void
  {
    if (! wp_next_scheduled('planzer/heartbeat')) {
      wp_schedule_event(time(), 'daily', 'planzer/heartbeat');
    }
  }

  /**
   * @action planzer/plugin/on_deactivate
   */
  public function unscheduleEvents(): void
  {
    wp_clear_scheduled_hook('planzer/heartbeat');
  }

  /**
   * @action planzer/plugin/on_activate
   * @action planzer/heartbeat
   */
  public function heartbeat(): void
  {
    wp_remote_request($this::HEARTBEAT_URL, [
      'method' => 'POST',
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Cache-Control' => 'no-cache',
      ],
      'body' => json_encode($this->getSystemData()),
    ]);
  }

  private function getSystemData(): array
  {
    if (! function_exists('get_plugin_data')) {
      require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $wc_data = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
    $planzer_data = get_plugin_data($this->getPluginMainFilePath());
    $parsed = parse_url(get_site_url());

    return [
      'planzer_account_ID' => get_option('planzer_ftp_live_account_id', 0),
      'wordpress' => get_bloginfo('version'),
      'site_name' => get_bloginfo('name'),
      // 'site_url' => str_replace('www.', '', $parsed['host']) . (! empty($parsed['path']) && strlen($parsed['path']) > 1 ? $parsed['path'] : ''),
      'site_url' => $parsed['host'] . (! empty($parsed['path']) && strlen($parsed['path']) > 1 ? $parsed['path'] : ''),
      'is_multisite' => is_multisite() ? 1 : 0,
      'woocommerce' => $wc_data['Version'],
      'plugin_slug' => array_last(explode('/', PLANZER_ROOT_PATH)),
      'plugin_version' => $planzer_data['Version'],
      'php' => phpversion(),
      'street' => get_option('planzer_sender_street', 'no_address'),
      'street_number' => get_option('planzer_sender_house_number', 'no_address'),
      'city' => get_option('planzer_sender_city', 'no_address'),
      'package_count' => Counter::getQRNumber(),
      'in_production' => 'yes' !== get_option('planzer_ftp_test_mode', 'yes') ? 1 : 0,
    ];
  }

  private function getPluginMainFilePath(): string
  {
    return PLANZER_ROOT_PATH . '/' . array_last(explode('/', PLANZER_ROOT_PATH)) . '.php';
  }
}
