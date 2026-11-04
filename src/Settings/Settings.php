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

  /**
   * 'none' disables an exclusion only on its own, so drop it as soon as products, methods or classes are selected.
   *
   * @filter woocommerce_admin_settings_sanitize_option_planzer_other_excluded_products
   * @filter woocommerce_admin_settings_sanitize_option_planzer_other_excluded_shipping
   * @filter woocommerce_admin_settings_sanitize_option_planzer_other_excluded_shipping_classes
   */
  public function sanitizeExcludedNone($value)
  {
    if (! is_array($value) || count($value) < 2) {
      return $value;
    }

    return array_values(array_diff($value, ['none']));
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
