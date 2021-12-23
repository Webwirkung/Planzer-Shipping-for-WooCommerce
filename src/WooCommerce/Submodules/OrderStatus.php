<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\CSV\CSV;
use Planzer\Note\Note;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;
use Planzer\QRCode\QRCode;
use Planzer\Note\NoteFactory;

use function Planzer\isTestModelEnabled;

class OrderStatus
{
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

    $order_items_id = array_map(fn ($item): int  => $item->get_product_id(), $order->get_items());
    $excluded_ids = get_option('planzer_other_excluded_products', []);
    if (
        ! in_array('none', $excluded_ids) &&
        empty(array_diff($order_items_id, $excluded_ids))
    ) {
      $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . __('All products excluded from delivery', 'planzer'));
      return;
    }

    if (isTestModelEnabled()) {
      $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . __('Test mode enabled - data not sent', 'planzer'));
      return;
    }

    $order_note = __('Planzer: CSV generated.', 'planzer');
    $package = new Package($order_id);

    $note = NoteFactory::create($order, $package, get_option('planzer_delivery_generate_note', 'label_note'));
    if (is_a($note, Note::class)) {
      $note->sendPdf($note->generatePDF());
      $order_note = __('Planzer: delivery/label note and CSV generated.', 'planzer');
    }

    try {
      $csv = new CSV($order, $package);
      $sftp = new SFTP();
      $sftp->upload($csv->getCsvContent(), $package);
      $order_note .= "<br><img src=\"{$package->getGeneratedQRUrl()}\"/>";
      $order_note .= "<br><br>" . __('Reference number:', 'planzer') . " {$order_id}_{$package->getSequenceNumber(0)}";
      $order_note .= "<br><br>" . __('Package number: ', 'planzer') . '<br>' . $package->getQRContentWithoutSuffix();
      $order->add_order_note($order_note);
    } catch (\Throwable $th) {
      $order->add_order_note('<span style="color:red;font-weight: bold;">Planzer: </span>' . __('There was an error while sending data to Planzer - please try again or check debuglog.', 'planzer'));
      error_log("FATAL ERROR {$th->getMessage()} in {$th->getFile()}:{$th->getLine()}");
    }
  }
}