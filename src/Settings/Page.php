<?php
namespace Planzer\Settings;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\Delivery as DeliverySection;
use Planzer\Settings\Sections\FTP as FTPSection;
use Planzer\Settings\Sections\Notifications as NotificationsSection;
use Planzer\Settings\Sections\Other as OtherSection;

class Page extends \WC_Settings_Page
{
  private array $sections = [];

  public function __construct()
  {
    $this->id = 'planzer';
    $this->label = PLANZER_NAME;
    $this->sections = [
      'sender' => createClass('Planzer\\Settings\\Sections\\Sender'),
      'ftp' => new FTPSection(),
      'notifications' => new NotificationsSection(),
      'delivery' => new DeliverySection(),
      'other' => new OtherSection(),
    ];
    parent::__construct();
  }

  protected function get_own_sections(): array // phpcs:ignore
  {
    return array_map(fn (Section $section) => $section->getLabel(), $this->sections);
  }

  protected function get_settings_for_default_section(): array // phpcs:ignore
  {
    return $this->sections['sender']->getSettings();
  }

  protected function get_settings_for_delivery_section(): array // phpcs:ignore
  {
    return $this->sections['delivery']->getSettings();
  }

  protected function get_settings_for_ftp_section(): array // phpcs:ignore
  {
    return $this->sections['ftp']->getSettings();
  }

  protected function get_settings_for_notifications_section(): array // phpcs:ignore
  {
    return $this->sections['notifications']->getSettings();
  }

  protected function get_settings_for_sender_section(): array // phpcs:ignore
  {
    return $this->sections['sender']->getSettings();
  }

  protected function get_settings_for_other_section(): array // phpcs:ignore
  {
    return $this->sections['other']->getSettings();
  }
}
