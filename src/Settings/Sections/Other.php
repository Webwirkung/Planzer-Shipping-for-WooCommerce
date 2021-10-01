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
        ->addMultiselectInput(__('Excluded products'), 'excluded_products', [
          'options' => $this->getProductsList(),
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
    return apply_filters('planzer/settings/other/excluded_products_list', $list);
  }
}
