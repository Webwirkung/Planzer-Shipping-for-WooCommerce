<?php

namespace Planzer\Note\Types;

use Planzer\Note\Templates\Template;
use Planzer\Note\Interfaces\Note as NoteInterface;
use Planzer\Note\Abstracts\Note as AbstractNote;

class DeliveryNote extends AbstractNote implements NoteInterface
{
  public function generatePDF(string $path = '/tmp/'): string
  {
    $company = get_option('planzer_sender_company_name');
    $countries = WC()->countries;
    $address = $countries->get_base_address() . ', ' . $countries->get_base_postcode() . ' ' . $countries->get_base_city();

    $this->getMpdf()->SetFooter(Template::generateTemplate('note/delivery-note/footer', [
      'company' => $company,
      'company_address' => $countries,
      'footer' => str_replace(['{company}', '{address}', '{email}', '{website}'], [$company, $address, get_option('planzer_sender_email'), site_url()], get_option('planzer_delivery_note_footer') ?: __('{company} {address} Email: {email} Website: {website}', 'planzer')),
    ]));

    $contactName = get_option('planzer_delivery_note_contact_name');
    $orderFirstName = $this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name();
    $orderLirstName = $this->order->get_shipping_last_name() ?: $this->order->get_billing_last_name();

    $this->getMpdf()->WriteHTML(Template::generateTemplate('note/delivery-note/content', [
      'order' => $this->order,
      'country' => $this->getFullCountryName($this->order->get_billing_country()),
      'package_number' => $this->package->getQRContentWithoutSuffix(),
      'qr_url' => $this->package->getGeneratedQRUrl(),
      'sequence_number' => $this->package->getSequenceNumber(0),
      'company_city' => get_option('planzer_sender_city'),
      'company' => $company,
      'company_extra' => get_option('planzer_sender_company_extra'),
      'company_address' => $countries,
      'logo' => get_option('planzer_delivery_note_logo'),
      'contact_name' => $contactName,
      'excluded_products_ids' => get_option('planzer_other_excluded_products', []),
      'salutation' => str_replace(['{first_name}', '{last_name}'], [$orderFirstName, $orderLirstName], get_option('planzer_delivery_note_salutation')) ?: __('Ladies and gentlemen', 'planzer'),
      'signature' => str_replace(['{contact_name}', '{company}'], [$contactName, $company], nl2br(get_option('planzer_delivery_note_signature') ?: str_replace('<br>', PHP_EOL, __('Thank you very much for the order.<br><br>Feel free to contact us if you have any questions.<br><br>Kind regards<br>{contact_name}<br>{company}', 'planzer')))),
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
