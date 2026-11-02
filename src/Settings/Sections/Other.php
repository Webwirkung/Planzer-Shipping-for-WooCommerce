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
        ->addMultiselectInput(__('Excluded shippings', 'planzer'), 'excluded_shipping', [
          'options' => $this->getShippingsList(),
          'default' => 'none',
          'desc' => __('Select shipping codes that should not use Planzer.', 'planzer'),
        ])
        ->addMultiselectInput(__('Excluded shipping classes', 'planzer'), 'excluded_shipping_classes', [
          'options' => $this->getShippingClassesList(),
          'default' => 'none',
          'desc' => __('Select shipping classes that should not use Planzer.', 'planzer') . '<br>' . __('(This option only works for flat rate shipping methods.)', 'planzer'),
        ])
        ->addMultiselectInput(__('Excluded products', 'planzer'), 'excluded_products', [
          'options' => $this->getSelectedProductsList(),
          'default' => [],
          'class' => 'wc-product-search',
          'css' => 'min-width: 350px;',
          'custom_attributes' => [
            'data-action' => 'woocommerce_json_search_products',
            'data-placeholder' => __('Search for a product&hellip;', 'woocommerce'),
          ],
          'desc' => __('Select product that are note being sent with Planzer.', 'planzer'),
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

  private function getSelectedProductsList(): array
  {
    // Only the saved products are preloaded, the rest comes from the AJAX search.
    $saved = (array) get_option('planzer_other_excluded_products', []);
    $ids = array_filter(array_map('absint', $saved));

    $list = [];
    // A saved 'none' disables the exclusion (1.0.35 behaviour), so keep it selectable until the admin removes it.
    if (in_array('none', $saved, true)) {
      $list['none'] = __('none', 'planzer');
    }
    foreach ($ids as $id) {
      $product = wc_get_product($id);
      if ($product) {
        $list[$id] = wp_strip_all_tags($product->get_formatted_name());
      }
    }

    return apply_filters('planzer/settings/other/excluded_products_list', $list);
  }

  private function getShippingsList(): array
  {
    $zones = \WC_Shipping_Zones::get_zones();

    $shippingMethods = [];

    foreach ($zones as $zoneItem) {
      foreach ($zoneItem['shipping_methods'] as $shippingItem) {
        if ('yes' === $shippingItem->enabled) {
          $shippingMethods[$shippingItem->title] = $shippingItem->title;
        }
      }
    }

    $shippingMethods = array_replace(['none' => __('none', 'planzer')], $shippingMethods);

    return apply_filters('planzer/settings/other/excluded_shipping_list', $shippingMethods);
  }

  private function getShippingClassesList(): array
  {
    $shipping_classes = WC()->shipping->get_shipping_classes();

    $shippingClasses = [];

    foreach ($shipping_classes as $shipping_class) {
      $shippingClasses[$shipping_class->term_id] = $shipping_class->name;
    }

    $shippingClasses = array_replace(['none' => __('none', 'planzer')], $shippingClasses);

    return apply_filters('planzer/settings/other/excluded_shippingclass_list', $shippingClasses);
  }
}
