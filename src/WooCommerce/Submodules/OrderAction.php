<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\CSV\CSV;
use WC_Order;
use Planzer\Note\NoteFactory;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;
use Planzer\Note\Note;
use Planzer\QRCode\Counter;
use Planzer\WooCommerce\Services\ExclusionService;

use function Planzer\isTestModelEnabled;

class OrderAction
{
  private $exclusionService;

  public function __construct(ExclusionService $exclusionService)
  {
      $this->exclusionService = $exclusionService;
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
    Counter::increaseQRNumber();
    $order_id = $order->get_id();
    $order_items_id = array_map(fn ($item): int  => $item->get_product_id(), $order->get_items());

    if ($exclusionReason = $this->exclusionService->getExclusionReason($order)) {
        $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . $exclusionReason);
        return;
    }

    $order_note = __('Planzer: CSV generated.', 'planzer');
    $package = new Package($order_id);

    $order->update_meta_data('planzer_tracking_code', $package->getQRContentWithoutSuffix());
    $order->save();

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

      if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $logger->error("FATAL ERROR: {$th->getMessage()} in {$th->getFile()} on line {$th->getLine()}", ['source' => 'wc-planzer-shipping']);
      }
    }
  }
}
