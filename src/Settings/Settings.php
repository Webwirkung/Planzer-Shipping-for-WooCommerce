<?php

namespace Planzer\Settings;

use Planzer\Settings\Fields\Toggle;
use Planzer\Settings\Page;

class Settings
{
  private const QR_SEQUENCE_NUMBER = 'planzer_qr_sequence_number';

  public function __construct()
  {
    createClass(Toggle::class);

    if (false === get_option(self::QR_SEQUENCE_NUMBER)) {
      $this->initializeDatabaseFields();
    }
  }

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

  private function initializeDatabaseFields(): void
  {
    update_option(self::QR_SEQUENCE_NUMBER, 0);
  }
}
