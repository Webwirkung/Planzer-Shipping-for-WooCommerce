<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\DeliveryNote\DeliveryNote;
use Planzer\Csv\Csv;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;

class OrderStatus
{
  /**
   * @action woocommerce_order_status_changed
   *
   * @return void
   */
  public function changeOrderStatus(int $order_id, string $old_status, string $new_status): void
  {
    if ('processing' === $new_status) {
      $order = wc_get_order($order_id);

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

      $csv = new Csv($order);
      $sftp = new SFTP();
      $sftp->upload($csv->getCsvContent(), new Package($order_id));
      $order->add_order_note($order_note);
    }
  }
}
