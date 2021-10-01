<?php
namespace Planzer\Settings;

use Planzer\Settings\Page;

class Settings
{
  /**
   * @filter woocommerce_get_settings_pages
   */
  public function addSettingsPage(array $settings): array
  {
    $settings[] = new Page();
    return $settings;
  }

  public function getSetting(string $key)
  {
    return ! empty($key) ? get_option("planzer_{$key}") : '';
  }
}
