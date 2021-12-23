<?php

namespace Planzer\Note\Types;

use Planzer\Note\Templates\Template;
use Planzer\Note\Interfaces\Note as NoteInterface;
use Planzer\Note\Abstracts\Note as AbstractNote;

class LabelNote extends AbstractNote implements NoteInterface
{
  public function generatePDF(string $path = '/tmp/'): string
  {
    $this->getMpdf()->WriteHTML(Template::generateTemplate('note/label-note/content', [
      'qr_url' => $this->package->getGeneratedQRUrl(),
      'order' => $this->order,
      'package_number' => $this->package->getQRContentWithoutSuffix(),
      'contact_name' => get_option('planzer_delivery_note_contact_name'),
      'sequence_number' => $this->package->getSequenceNumber(0),
    ]));

    $full_path = "$path{$this->package->getPackageNumber()}.pdf";
    $this->getMpdf()->Output($full_path, 'F');

    return $full_path;
  }

  public function getType(): string
  {
    return "label_note";
  }
}
