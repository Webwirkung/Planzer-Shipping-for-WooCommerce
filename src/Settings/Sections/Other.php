<?php
namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class Other extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup(__('Other', 'planzer'), 'other')
        ->addMultiselectInput(__('Excluded products', 'planzer'), 'excluded_products', [
          'options' => $this->getProductsList(),
          'default' => 'none',
          'desc' => __('Select product that are note being sent with Planzer.', 'planzer'),
        ])
        ->addMultiselectInput(__('Excluded shippings', 'planzer'), 'excluded_shipping', [
          'options' => $this->getShippingsList(),
          'default' => 'none',
          'desc' => __('Select shipping codes that should not use Planzer.', 'planzer'),
        ])
      ->endGroup('other');
  }

  public function getId(): string
  {
    return 'other';
  }

  public function getLabel(): string
  {
    return __('Other', 'planzer');
  }

  private function getProductsList(): array
  {
    $list = get_posts([
      'post_type' => 'product',
      'numberposts' => -1,
      'post_status' => 'publish',
      'fields' => 'ids',
    ]);
    $list = array_flip($list);
    array_walk($list, function (&$value, $key) {
      $value = get_the_title($key);
    });

    $list = array_replace(['none' => __('none', 'planzer')], $list);

    return apply_filters('planzer/settings/other/excluded_products_list', $list);
  }

  private function getShippingsList(): array
  {
    $zones = \WC_Shipping_Zones::get_zones();

    $shippingMethods = [];
    foreach ($zones as $zoneItem) {
      foreach ($zoneItem['shipping_methods'] as $shippingItem) {
        if ('yes' === $shippingItem->enabled) {
          $shippingMethods[$shippingItem->id] = $shippingItem->title;
        }
      }
    }

    $shippingMethods = array_replace(['none' => __('none', 'planzer')], $shippingMethods);

    return apply_filters('planzer/settings/other/excluded_shipping_list', $shippingMethods);
  }
}
