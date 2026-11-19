<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\WooCommerce\Services\Transmitter;

class OrderStatus
{
  private Transmitter $transmitter;

  public function __construct(Transmitter $transmitter)
  {
    $this->transmitter = $transmitter;
  }

  /**
   * @action woocommerce_order_status_changed
   *
   * @return void
   */
  public function onOrderStatusChanged(int $order_id, string $old_status, string $new_status): void
  {
    switch ($new_status) {
      case 'processing':
        if ($this->shouldSendDataToPlanzerWhenProcessing()) {
          $this->sendOrderToPlanzer($order_id);
        }
          break;
      case 'planzer-transmit':
        $this->sendOrderToPlanzer($order_id);
          break;
      default:
        // do nothing
          break;
    }
  }

  /**
   * @action woocommerce_register_shop_order_post_statuses
   */
  public function addCustomOrderStatus(array $statuses): array
  {
    $statuses['wc-planzer-transmit'] = [
      'label' => __('Transmit to Planzer', 'planzer'),
      'public' => false,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Transmit to Planzer (%s)', 'Transmit to Planzer (%s)', 'planzer')
    ];
    return $statuses;
  }

  /**
   * @filter wc_order_statuses
   */
  public function listCustomOrderStatus(array $statuses): array
  {
    if (! $this->shouldSendDataToPlanzerWhenProcessing()) {
      $statuses['wc-planzer-transmit'] = __('Transmit to Planzer', 'planzer');
    }
    return $statuses;
  }

  private function shouldSendDataToPlanzerWhenProcessing(): bool
  {
    return 'yes' !== get_option('planzer_sender_enable_manual_orders', 'no');
  }

  private function sendOrderToPlanzer(int $order_id): void
  {
    $order = wc_get_order($order_id);

    if ($order) {
      $this->transmitter->transmit($order);
    }
  }
}
