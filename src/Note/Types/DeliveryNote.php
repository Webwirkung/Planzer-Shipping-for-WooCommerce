<?php

namespace Planzer\Note\Types;

use Planzer\Note\Templates\Template;
use Planzer\Note\Interfaces\Note as NoteInterface;
use Planzer\Note\Abstracts\Note as AbstractNote;

class DeliveryNote extends AbstractNote implements NoteInterface
{
  public function generatePDF(string $path = '/tmp/'): string
  {
    $this->getMpdf()->SetFooter(Template::generateTemplate('note/delivery-note/footer', [
      'company' => get_option('planzer_sender_company_name'),
      'company_address' => WC()->countries,
      'email' => get_option('planzer_sender_email'),
      'website' => site_url(),
    ]));

    $this->getMpdf()->WriteHTML(Template::generateTemplate('note/delivery-note/content', [
      'order' => $this->order,
      'country' => $this->getFullCountryName($this->order->get_billing_country()),
      'package_number' => $this->package->getQRContentWithoutSuffix(),
      'qr_url' => $this->package->getGeneratedQRUrl(),
      'sequence_number' => $this->package->getSequenceNumber(0),
      'company_city' => get_option('planzer_sender_city'),
      'company' => get_option('planzer_sender_company_name'),
      'company_extra' => get_option('planzer_sender_company_extra'),
      'company_address' => WC()->countries,
      'logo' => get_option('planzer_delivery_note_logo'),
      'contact_name' => get_option('planzer_delivery_note_contact_name'),
      'excluded_products_ids' => get_option('planzer_other_excluded_products'),
    ]));

    $full_path = "$path{$this->package->getPackageNumber()}.pdf";
    $this->getMpdf()->Output($full_path, 'F');

    return $full_path;
  }

  public function getType(): string
  {
    return "delivery_note";
  }
}
