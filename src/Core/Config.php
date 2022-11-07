<?php

namespace Planzer\Core;

class Config
{
  /**
   * @action plugins_loaded
   */
  public function initConfig(): void
  {
    if (! load_textdomain('planzer', PLANZER_RESOURCES_PATH . '/lang/' . get_user_locale() . '.mo')) {
      load_textdomain('planzer', PLANZER_RESOURCES_PATH . '/lang/' . explode('_', get_user_locale())[0] . '.mo');
    }
  }

  /**
   * @action wp_enqueue_scripts
   */
  public function dependencies(): void
  {
    $version = 'production' === wp_get_environment_type() ? null : time();

    /**
     * MS: PLZ-51 - disable loading assets since they are empty anyway
     * when enabling make sure they will work for WPML setup like:
     * - example.test/en/wp-content/plugins/wc-planzer-shipping/dist/styles/admin.css
     * - example.test/de/wp-content/plugins/wc-planzer-shipping/dist/styles/admin.css
     */

    // wp_enqueue_style('planzer/front.css', PLANZER_ASSETS_URI . '/styles/front.css', false, $version);
    // wp_enqueue_script('planzer/manifest.js', PLANZER_ASSETS_URI . '/scripts/manifest.js', ['jquery'], $version, true);
    // wp_enqueue_script('planzer/vendor.js', PLANZER_ASSETS_URI . '/scripts/vendor.js', ['planzer/manifest.js'], $version, true);
    // wp_enqueue_script('planzer/front.js', PLANZER_ASSETS_URI . '/scripts/front.js', ['planzer/manifest.js'], $version, true);

    wp_localize_script('planzer/front.js', 'planzer', [
      'ajax' => admin_url('admin-ajax.php')
    ]);
  }

  /**
   * @action admin_enqueue_scripts
   */
  public function adminDependencies(): void
  {
    $version = 'production' === wp_get_environment_type() ? null : time();

    wp_enqueue_style('planzer/admin.css', PLANZER_ASSETS_URI . '/styles/admin.css', false, $version);
    wp_enqueue_script('planzer/manifest.js', PLANZER_ASSETS_URI . '/scripts/manifest.js', ['jquery'], $version, true);
    wp_enqueue_script('planzer/vendor.js', PLANZER_ASSETS_URI . '/scripts/vendor.js', ['planzer/manifest.js'], $version, true);
    wp_enqueue_script('planzer/admin.js', PLANZER_ASSETS_URI . '/scripts/admin.js', ['planzer/manifest.js'], $version, true);

    wp_localize_script('planzer/admin.js', 'planzer', [
      'ajax' => admin_url('admin-ajax.php')
    ]);
  }

  /**
   * @filter plugin_action_links_wc-planzer-shipping/wc-planzer-shipping.php
   */
  public function settingsLink($links)
  {
    $url = esc_url(add_query_arg(
        [
          'page' => 'wc-settings',
          'tab' => 'planzer',
          'section' => 'sender',
        ],
        get_admin_url() . 'admin.php'
    ));
    $settings_link = "<a href='$url'>" . __('Settings', 'planzer') . '</a>';
    array_push(
        $links,
        $settings_link
    );
    return $links;
  }
}
