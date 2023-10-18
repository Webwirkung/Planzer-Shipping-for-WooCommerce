<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\CSV\CSV;
use Planzer\Note\NoteFactory;
use Planzer\SFTP\SFTP;
use Planzer\Package\Package;
use Planzer\Note\Note;
use Planzer\QRCode\Counter;

use function Planzer\isTestModelEnabled;

class BulkAction
{
  /**
   * @filter bulk_actions-edit-shop_order
   */
  public function addCustomBulkAction(array $actions): array
  {
    if ('yes' === get_option('planzer_sender_enable_manual_orders', 'no')) {
      $actions['wc-planzer-transmit'] = __('Transmit to Planzer', 'planzer');
    }

    return $actions;
  }

  /**
   * @action admin_notices
   */
  public function planzerTransmitBulkActionNotice(): void
  {
    if (empty($_REQUEST['wc-planzer-transmit'])) {
      return;
    }

    $status = (5 >= $_REQUEST['posts-ids-count']) ? 'success' : 'warning';
    $message = (5 >= $_REQUEST['posts-ids-count']) ? __("The orders with number <b>%s</b> have proceeded to the planzer.", 'planzer') : __("The maximum limit of orders to sent to the planzer at one time is 5. We sent 5 orders: <b>%s</b>. For the rest of the orders not processed please choose them again", 'planzer');

    printf("<div id='message' class='notice notice-%s is-dismissible'><p>%s</p></div>", $status, sprintf($message, $_REQUEST['processed-ids']));
  }

  /**
   * @filter handle_bulk_actions-edit-shop_order
   */
  public function handlePlanzerTransmitBulkAction(string $redirectTo, string $action, array $postIds): string
  {
    if ('wc-planzer-transmit' !== $action) {
      return $redirectTo;
    }

    $postsSliced = array_slice($postIds, 0, 5);

    foreach ($postsSliced as $id) {
      Counter::increaseQRNumber();
      $order = wc_get_order($id);

      if ('planzer-transmit' !== $order->get_status()) {
        $order->update_status('wc-planzer-transmit');
        continue;
      }

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
        return $redirectTo;
      }

      if (isTestModelEnabled()) {
        $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . __('Test mode enabled - data not sent', 'planzer'));
        return $redirectTo;
      }

      $order_note = __('Planzer: CSV generated.', 'planzer');
      $package = new Package($id);

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
        $order_note .= "<br><br>" . __('Reference number:', 'planzer') . " {$id}_{$package->getSequenceNumber(0)}";
        $order_note .= "<br><br>" . __('Package number: ', 'planzer') . '<br>' . $package->getQRContentWithoutSuffix();
        $order->add_order_note($order_note);
      } catch (\Throwable $th) {
        $order->add_order_note('<span style="color:red;font-weight: bold;">Planzer: </span>' . __('There was an error while sending data to Planzer - please try again or check debuglog.', 'planzer'));
        error_log("FATAL ERROR {$th->getMessage()} in {$th->getFile()}:{$th->getLine()}");
      }
    }

    return add_query_arg([
      'wc-planzer-transmit' => '1',
      'processed-ids' => implode(',', $postsSliced),
      'posts-ids-count' => count($postIds),
    ], $redirectTo);
  }
}
