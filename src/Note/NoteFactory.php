<?php

namespace Planzer\Note;

use WC_Order;
use Planzer\Note\Types\DeliveryNote;
use Planzer\Note\Types\LabelNote;
use Planzer\Package\Package;

class NoteFactory
{
  public static function create(WC_Order $order, Package $package, string $note_type): ?Note
  {
    switch ($note_type) {
      case 'delivery_note':
          $note = new DeliveryNote($order, $package, ['format' => 'A4', 'orientation' => 'P', 'margin_top' => 5]);
          break;
      case 'label_note':
        $note = new LabelNote($order, $package, ['format' => 'A6', 'orientation' => 'L']);
          break;
      default:
          return null;
    }

    return new Note($order, $note);
  }
}
