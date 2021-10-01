<?php

namespace Planzer\DeliveryNote;

use Mpdf\Mpdf;
use WC_Order;
use Planzer\QRCode\QRCode;
use Planzer\DeliveryNote\Template;
use Planzer\Package\Package;

class DeliveryNote
{
  private Mpdf $mpdf;
  private string $body = '';
  private WC_Order $order;

  public function __construct(WC_Order $order)
  {
    $this->mpdf = new Mpdf();
    $this->mpdf->defaultfooterline = 0;
    $this->order = $order;
  }

  public function getMpdf(): Mpdf
  {
    return $this->mpdf;
  }

  public function addToBody(string $text): self
  {
    $this->body .= $text;

    return $this;
  }

  public function getBody(): string
  {
    return $this->body;
  }

  public function generatePDF(string $path = '/tmp/'): string
  {
    $package = new Package($this->order->get_id());
    $qr_code = new QRCode();

    $blog_name = get_bloginfo('name');

    $this->getMpdf()->SetFooter(Template::generateTemplate('delivery-note/footer', [
        'company' => $blog_name,
        'company_address' => WC()->countries,
        'email' => get_option('admin_email'),
        'website' => site_url(),
      ]));

    $this->getMpdf()->WriteHTML(Template::generateTemplate('delivery-note/content', [
        'order' => $this->order,
        'country' => $this->getFullCountryName($this->order->get_billing_country()),
        'package_number' => $package->getPackageNumber(),
        'qr_url' => $qr_code->getGeneratedQRUrl($package->getQRContent()),
        'sequence_number' => $package->getSequenceNumber(),
        'company' => $blog_name,
        'company_address' => WC()->countries,
        'logo' => get_option('planzer_delivery_note_logo'),
        'contact_name' => get_option('planzer_delivery_note_contact_name'),
        'excluded_products_ids' => get_option('planzer_other_excluded_products'),
      ]));

    $full_path = "$path{$package->getPackageNumber()}.pdf";
    $this->getMpdf()->Output($full_path, 'F');

    return $full_path;
  }

  private function getFullCountryName(string $country_code): string
  {
    $wc_countries = WC()->countries->countries;

    if (array_key_exists($country_code, $wc_countries)) {
      $country = $wc_countries[$country_code];
    } else {
      $country = $country_code;
    }

    return $country;
  }

  public function sendPdf(string $pdf_path): void
  {
    $emails = get_option('planzer_delivery_note_email');

    if (! empty($emails)) {
      $emails_array = explode(',', $emails);

      foreach ($emails_array as $email) {
        if (! empty($email)) {
          $result = wp_mail($email, $this->order->get_id(), ' ', '', [$pdf_path]);

          if (! $result) {
            error_log('Planzer: ERROR while sending Planzer PDF email - ' . substr($email, 0, 4) . '...');
            $this->order->add_order_note(__(sprintf('Planzer: ERROR while sending Planzer PDF email - %s', substr($email, 0, 4) . '...')));
          }
        }
      }
    }
    unlink($pdf_path);
  }
}
