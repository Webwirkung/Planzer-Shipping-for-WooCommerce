<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\CSV\CSV;
use WC_Order;
use Planzer\Note\NoteFactory;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;
use Planzer\Note\Note;
use Planzer\QRCode\Counter;

use function Planzer\isTestModelEnabled;

class OrderAction
{
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
    Counter::increaseQRNumber();
    $order_id = $order->get_id();
    $order_items_id = array_map(fn ($item): int  => $item->get_product_id(), $order->get_items());

    $excluded_ids = get_option('planzer_other_excluded_products', []);
    if ('none' === $excluded_ids || false === $excluded_ids) {
      $excluded_ids = ['none'];
    }

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
      error_log("FATAL ERROR: {$th->getMessage()} in {$th->getFile()} on line {$th->getLine()}");
    }
  }
}
