<?php
namespace Planzer;

use Planzer\Core\Singleton;

class Init extends Singleton
{
  public array $public = [];

  public array $private = [];

  public function __construct()
  {
    $this->addPrivate('Core\Config');
    $this->addPublic('Settings\Settings', 'settings');
    $this->addPublic('WooCommerce\WooCommerce');
  }

  private function addPublic(string $name, string $label = ''): void
  {
    $class = 'Planzer\\' . $name;
    $index = ! empty($label) ? $label : $name;
    $this->public[$index] = new $class();
    planzerDoc()->addDocHooks($this->public[$index]);
  }

  private function addPrivate(string $name, string $label = ''): void
  {
    $class = 'Planzer\\' . $name;
    $index = ! empty($label) ? $label : $name;
    $this->private[$index] = new $class();
    planzerDoc()->addDocHooks($this->private[$index]);
  }
}
