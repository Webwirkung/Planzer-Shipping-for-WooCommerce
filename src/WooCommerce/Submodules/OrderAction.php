<?php

namespace Planzer\WooCommerce\Submodules;

use WC_Order;
use Planzer\DeliveryNote\DeliveryNote;
use Planzer\Csv\Csv;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;

class OrderAction
{
  /**
   * @action woocommerce_order_actions
   */
  public function addOrderActions(array $actions): array
  {
    $actions['planzer_generate_delivery_note'] = __('Generate Planzer deliviery note and CSV file', 'planzer');
    return $actions;
  }

  /**
   * @action woocommerce_order_action_planzer_generate_delivery_note
   */
  public function handleGenerateDeliveryNoteAction(WC_Order $order): void
  {
    $order_id = $order->get_id();

    $order_items_id = array_map(fn ($item): int  => $item->get_product_id(), $order->get_items());
    $diff_ids = array_diff($order_items_id, get_option('planzer_other_excluded_products'));

    if (empty($diff_ids)) {
      $order->add_order_note(__('Planzer: All products excluded from delivery', 'planzer'));
      return;
    }

    $order_note = __('Planzer: CSV generated.', 'planzer');

    if ('yes' === get_option('planzer_delivery_generate_note')) {
      $delivery_note = new DeliveryNote($order);
      $delivery_note->sendPdf($delivery_note->generatePDF());
      $order_note = __('Planzer: delivery note and CSV generated.', 'planzer');
    }

    $csv = new Csv(wc_get_order($order_id));
    $sftp = new SFTP();
    $sftp->upload($csv->getCsvContent(), new Package($order_id));
    $order->add_order_note($order_note);
  }
}
