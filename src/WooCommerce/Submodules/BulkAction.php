<?php

namespace Planzer\WooCommerce\Submodules;

use Planzer\WooCommerce\Services\ExclusionService;
use Planzer\WooCommerce\Services\Transmitter;

class BulkAction
{
  private ExclusionService $exclusionService;
  private Transmitter $transmitter;

  public function __construct(ExclusionService $exclusionService, Transmitter $transmitter)
  {
    $this->exclusionService = $exclusionService;
    $this->transmitter = $transmitter;
  }

  /**
   * @filter bulk_actions-edit-shop_order
   * @filter bulk_actions-woocommerce_page_wc-orders
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

    $processedIds = $this->idsFromRequest('processed-ids');
    $excludedIds = $this->idsFromRequest('excluded-ids');
    $failedIds = $this->idsFromRequest('failed-ids');

    if (! empty($processedIds)) {
      printf("<div id='message' class='notice notice-%s is-dismissible'><p>%s</p></div>", $status, sprintf($message, esc_html(implode(',', $processedIds))));
    }

    if (! empty($excludedIds)) {
      printf("<div class='notice notice-info is-dismissible'><p>%s</p></div>", sprintf(__('These orders were not sent to Planzer because they are excluded from Planzer delivery: <b>%s</b>', 'planzer'), esc_html(implode(',', $excludedIds))));
    }

    if (! empty($failedIds)) {
      printf("<div class='notice notice-error is-dismissible'><p>%s</p></div>", sprintf(__('These orders could not be sent to Planzer, see the order notes: <b>%s</b>', 'planzer'), esc_html(implode(',', $failedIds))));
    }
  }

  /**
   * @return int[]
   */
  private function idsFromRequest(string $key): array
  {
    return array_values(array_filter(array_map('absint', explode(',', (string) ($_REQUEST[$key] ?? '')))));
  }

  /**
   * @filter handle_bulk_actions-edit-shop_order
   * @filter handle_bulk_actions-woocommerce_page_wc-orders
   */
  public function handlePlanzerTransmitBulkAction(?string $redirectTo, string $action, array $postIds): string
  {
    $redirectTo = $redirectTo ?? admin_url('edit.php?post_type=shop_order');

    if ('wc-planzer-transmit' !== $action) {
      return $redirectTo;
    }

    $postsSliced = array_slice($postIds, 0, 5);
    $processedIds = [];
    $excludedIds = [];
    $failedIds = [];

    foreach ($postsSliced as $id) {
      $order = wc_get_order($id);

      if (! $order) {
        $failedIds[] = $id;
        continue;
      }

      // Excluded orders keep their status and get a note.
      if ($exclusionReason = $this->exclusionService->getExclusionReason($order)) {
        $order->add_order_note('<span style="color:#0070ff;font-weight: bold;">Planzer: </span>' . $exclusionReason);
        $excludedIds[] = $id;
        continue;
      }

      if ('planzer-transmit' !== $order->get_status()) {
        // The status change sends the order (OrderStatus); the counter is increased there, once.
        $order->update_status('wc-planzer-transmit');
        $result = $this->transmitter->getResult($order->get_id());
      } else {
        $result = $this->transmitter->transmit($order);
      }

      if (in_array($result, [Transmitter::RESULT_SENT, Transmitter::RESULT_TEST], true)) {
        $processedIds[] = $id;
      } elseif (Transmitter::RESULT_EXCLUDED === $result) {
        $excludedIds[] = $id;
      } else {
        $failedIds[] = $id;
      }
    }

    return add_query_arg([
      'wc-planzer-transmit' => '1',
      'processed-ids' => implode(',', $processedIds),
      'excluded-ids' => implode(',', $excludedIds),
      'failed-ids' => implode(',', $failedIds),
      'posts-ids-count' => count($postIds),
    ], $redirectTo);
  }
}
