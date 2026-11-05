<?php

namespace Planzer\Settings;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\Delivery as DeliverySection;
use Planzer\Settings\Sections\FTP as FTPSection;
use Planzer\Settings\Sections\Notifications as NotificationsSection;
use Planzer\Settings\Sections\Other as OtherSection;
use Planzer\Settings\Sections\Sender as SenderSection;

class Page extends \WC_Settings_Page
{
  private const SECTIONS = [
    'sender' => SenderSection::class,
    'ftp' => FTPSection::class,
    'notifications' => NotificationsSection::class,
    'delivery' => DeliverySection::class,
    'other' => OtherSection::class,
  ];

  public function __construct()
  {
    $this->id = 'planzer';
    $this->label = PLANZER_NAME;
    parent::__construct();
  }

  protected function get_own_sections(): array // phpcs:ignore
  {
    return [
      'sender' => __('Sender', 'planzer'),
      'ftp' => __('FTP', 'planzer'),
      'notifications' => __('Notifications', 'planzer'),
      'delivery' => __('Delivery', 'planzer'),
      'other' => __('Other', 'planzer'),
    ];
  }

  protected function get_settings_for_default_section(): array // phpcs:ignore
  {
    return $this->getSection('sender')->getSettings();
  }

  protected function get_settings_for_delivery_section(): array // phpcs:ignore
  {
    return $this->getSection('delivery')->getSettings();
  }

  protected function get_settings_for_ftp_section(): array // phpcs:ignore
  {
    return $this->getSection('ftp')->getSettings();
  }

  protected function get_settings_for_notifications_section(): array // phpcs:ignore
  {
    return $this->getSection('notifications')->getSettings();
  }

  protected function get_settings_for_sender_section(): array // phpcs:ignore
  {
    return $this->getSection('sender')->getSettings();
  }

  protected function get_settings_for_other_section(): array // phpcs:ignore
  {
    return $this->getSection('other')->getSettings();
  }

  // Not cached: WooCommerce saves and then renders with the same page, the render must see the saved values.
  private function getSection(string $id): Section
  {
    $class = self::SECTIONS[$id];
    // Sender registers its own hooks (sender country sanitizing), so it goes through createClass.
    return 'sender' === $id ? createClass($class) : new $class();
  }
}
