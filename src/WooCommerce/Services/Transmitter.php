<?php

namespace Planzer\WooCommerce\Services;

use Planzer\CSV\CSV;
use Planzer\Note\Note;
use Planzer\Note\NoteFactory;
use Planzer\Package\Package;
use Planzer\QRCode\Counter;
use Planzer\SFTP\SFTP;
use WC_Order;

use function Planzer\isTestModelEnabled;

/**
 * Sends one order to Planzer. The order status hook, the order action and the bulk action all go through here, so
 * test mode, exclusions and the QR counter behave the same everywhere.
 */
class Transmitter
{
  public const RESULT_EXCLUDED = 'excluded';
  public const RESULT_TEST = 'test';
  public const RESULT_SENT = 'sent';
  public const RESULT_FAILED = 'failed';

  private ExclusionService $exclusionService;

  /**
   * The result per order ID within this request, so the bulk action can tell what the status hook did.
   *
   * @var array<int, string>
   */
  private array $results = [];

  public function __construct(ExclusionService $exclusionService)
  {
    $this->exclusionService = $exclusionService;
  }

  public function transmit(WC_Order $order): string
  {
    return $this->results[$order->get_id()] = $this->send($order);
  }

  public function getResult(int $orderId): ?string
  {
    return $this->results[$orderId] ?? null;
  }

  private function send(WC_Order $order): string
  {
    $orderId = $order->get_id();

    // Once per attempt, also for excluded orders and in test mode, as before. The bulk action no longer adds its own.
    Counter::increaseQRNumber();

    if ($exclusionReason = $this->exclusionService->getExclusionReason($order)) {
      $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . $exclusionReason);

      return self::RESULT_EXCLUDED;
    }

    if (isTestModelEnabled()) {
      $package = new Package($orderId);

      $order->update_meta_data('planzer_tracking_code', 'TEST_' . $package->getQRContentWithoutSuffix());
      $order->save();

      $note = NoteFactory::create($order, $package, get_option('planzer_delivery_generate_note', 'label_note'));
      if (is_a($note, Note::class)) {
        $note->sendPdf($note->generatePDF());
      }

      $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . __('Test mode enabled - data not sent to Planzer. Demo delivery note generated and sent.', 'planzer'));

      return self::RESULT_TEST;
    }

    $orderNote = __('Planzer: CSV generated.', 'planzer');
    $package = new Package($orderId);

    $order->update_meta_data('planzer_tracking_code', $package->getQRContentWithoutSuffix());
    $order->save();

    $note = NoteFactory::create($order, $package, get_option('planzer_delivery_generate_note', 'label_note'));
    if (is_a($note, Note::class)) {
      $note->sendPdf($note->generatePDF());
      $orderNote = __('Planzer: delivery/label note and CSV generated.', 'planzer');
    }

    try {
      $csv = new CSV($order, $package);
      $sftp = new SFTP();
      $sftp->upload($csv->getCsvContent(), $package);
      $orderNote .= "<br><img src=\"{$package->getGeneratedQRUrl()}\"/>";
      $orderNote .= "<br><br>" . __('Reference number:', 'planzer') . " {$orderId}_{$package->getSequenceNumber(0)}";
      $orderNote .= "<br><br>" . __('Package number: ', 'planzer') . '<br>' . $package->getQRContentWithoutSuffix();
      $orderNote .= "<br><br><a href='https://tracking.app.planzer.ch/delivery/info?packageNumber=" . $package->getQRContentWithoutSuffix() . "&system=1' target='_blank'>" . __('Track package', 'planzer') . "</a>";

      $order->add_order_note($orderNote);

      return self::RESULT_SENT;
    } catch (\Throwable $th) {
      $order->add_order_note('<span style="color:red;font-weight: bold;">Planzer: </span>' . __('There was an error while sending data to Planzer - please try again or check debuglog.', 'planzer'));

      if (function_exists('wc_get_logger')) {
        wc_get_logger()->error("FATAL ERROR: {$th->getMessage()} in {$th->getFile()} on line {$th->getLine()}", ['source' => 'wc-planzer-shipping']);
      }

      return self::RESULT_FAILED;
    }
  }
}
