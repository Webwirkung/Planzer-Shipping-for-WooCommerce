<?php

namespace Planzer\WooCommerce\Submodules;

use WC_Order;
use Planzer\WooCommerce\Services\Transmitter;

class OrderAction
{
  private Transmitter $transmitter;

  public function __construct(Transmitter $transmitter)
  {
    $this->transmitter = $transmitter;
  }

  /**
   * @action woocommerce_order_actions
   */
  public function addOrderActions(array $actions): array
  {
    $actions['planzer_generate_delivery_note'] = __('Generate Planzer note and create package', 'planzer');
    return $actions;
  }

  /**
   * @action woocommerce_order_action_planzer_generate_delivery_note
   */
  public function handleGenerateDeliveryNoteAction(WC_Order $order): void
  {
    $this->transmitter->transmit($order);
  }

  /**
   * @action woocommerce_order_details_after_order_table
   */
  public function addTrackingLinkToOrder(WC_Order $order): void
  {
    $packageNumber = $order->get_meta('planzer_tracking_code');

    if ($packageNumber) {
      $trackingUrl = 'https://tracking.app.planzer.ch/delivery/info?packageNumber=' . $packageNumber . '&system=1';

      echo '<p><strong>' . __('Track your package: ', 'planzer') . '</strong> <a href="' . esc_url($trackingUrl) . '" target="_blank">' . __('Click here', 'planzer') . '</a></p>';
    }
  }

  /**
   * @filter woocommerce_email_order_meta
   */
  public function addTrackingLinkToEmail(WC_Order $order, $sent_to_admin, $plain_text)
  {
    $packageNumber = $order->get_meta('planzer_tracking_code');

    if ($packageNumber && ! $sent_to_admin) {
      $trackingUrl = 'https://tracking.app.planzer.ch/delivery/info?packageNumber=' . $packageNumber . '&system=1';

      echo '<p><strong>' . __('Track your package: ', 'planzer') . '</strong> <a href="' . esc_url($trackingUrl) . '" target="_blank">' . __('Click here', 'planzer') . '</a></p><br>';
    }
  }
}
