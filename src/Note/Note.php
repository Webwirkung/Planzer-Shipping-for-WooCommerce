<?php

namespace Planzer\Note;

use WC_Order;
use Planzer\Note\Interfaces\Note as NoteInterface;

class Note
{
  private NoteInterface $note;

  private WC_Order $order;

  public function __construct(WC_Order $order, NoteInterface $note)
  {
    $this->order = $order;
    $this->note = $note;
  }

  public function generatePDF(): string
  {
    return $this->note->generatePDF();
  }

  public function sendPdf(string $pdf_path): void
  {
    $emails = get_option('planzer_delivery_note_email');

    if (! empty($emails)) {
      $emails_array = explode(',', $emails);

      foreach ($emails_array as $email) {
        if (! empty($email)) {
          switch ($this->note->getType()) {
            case 'delivery_note':
                $subject = __('Planzer delivery note for order', 'planzer');
                break;
            case 'label_note':
                $subject = __('Planzer label note for order', 'planzer');
                break;
            default:
                $subject = __('Planzer note for order', 'planzer');
                break;
          }

          $subject .= " {$this->note->getReferenceNumber()}";
          $result = wp_mail($email, $subject, ' ', '', [$pdf_path]);

          if (! $result) {
            error_log('Planzer: ERROR while sending Planzer PDF email - ' . substr($email, 0, 4) . '...');
            $this->order->add_order_note(sprintf(__('Planzer: ERROR while sending Planzer PDF email - %s', 'planzer'), substr($email, 0, 4) . '...'));
          }
        }
      }
    }
    unlink($pdf_path);
  }
}
