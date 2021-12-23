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
          $note = new DeliveryNote($order, $package);
          break;
      case 'label_note':
        $note = new LabelNote($order, $package, 'A5');
          break;
      default:
          return null;
    }

    return new Note($order, $note);
  }
}
