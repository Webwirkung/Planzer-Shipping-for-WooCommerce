<?php
namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class Sender extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup(__('Contract Details', 'planzer'), 'contract_details')
        ->addNumberInput(__('Depart. No.', 'planzer'), 'department_number', [
          'custom_attributes' => [
            'min' => 0,
            'max' => 999999,
            'step' => 1,
          ],
          'required' => true,
        ])
        ->addNumberInput(__('Customer No.', 'planzer'), 'customer_number', [
          'custom_attributes' => [
            'min' => 0,
            'max' => 999999,
            'step' => 1,
          ],
          'required' => true,
        ])
        ->addSelectInput(__('Your branch', 'planzer'), 'control_number', [
          'default' => 57,
          'options' => [
            '50' => __('Dietikon', 'planzer'),
            '51' => __('Pratteln / Basel', 'planzer'),
            '52' => __('Bioggio', 'planzer'),
            '53' => __('Bern', 'planzer'),
            '54' => __('Penthalaz', 'planzer'),
            '55' => __('Conthey', 'planzer'),
            '56' => __('Domat / Ems', 'planzer'),
            '57' => __('Altstetten / Zürich', 'planzer'),
            '58' => __('Carouge', 'planzer'),
            '59' => __('Märstetten', 'planzer'),
            '60' => __('Seewen', 'planzer'),
          ],
          'required' => true,
        ])
      ->endGroup('contract_details')

      ->startGroup(__('Sender Information', 'planzer'), 'sender_information')
        ->addTextInput(__('Company', 'planzer'), 'company_name', ['required' => true])
        ->addTextInput(__('Company extra', 'planzer'), 'company_extra')
      ->endGroup('sender_information')

      ->startGroup('', 'shop_data')
        ->addTextInput(__('Street', 'planzer'), 'street', [
          'required' => true,
          'default' => get_option('woocommerce_store_address')
        ])
        ->addTextInput(__('House Number', 'planzer'), 'house_number', [
          'required' => true,
          'default' => get_option('')
        ])
        ->addTextInput(__('ZIP', 'planzer'), 'zip', [
          'required' => true,
          'default' => get_option('woocommerce_store_postcode')
        ])
        ->addTextInput(__('City', 'planzer'), 'city', [
          'required' => true,
          'default' => get_option('woocommerce_store_city'),
        ])
        ->addSelectInput(__('Country', 'planzer'), 'country', [
          'default' => 'CH',
          'options' => [
            'CH' => __('Schweiz / Switzerland', 'country name', 'planzer'),
          ],
          'disabled' => 'disabled',
        ])
        ->addSelectInput(__('Language', 'planzer'), 'language', [
          'default' => get_option('woocommerce_default_language'),
          'options' => $this->getLanguages(),
        ])
        ->addTextInput(__('Instruction', 'planzer'), 'instruction')
        ->addEmailInput(__('E-Mail', 'planzer'), 'email', [
          'default' => get_option('admin_email'),
          'required' => true,
        ])
        ->addTextInput(__('Phone', 'planzer'), 'phone')
        ->addTextInput(__('Mobile', 'planzer'), 'mobile')
      ->endGroup('shop_data')

      ->startGroup('', 'address_verification')
        ->addCheckbox(__('Automatic address verification (experimental feature)', 'planzer'), 'verify_address', [
          'desc' => __('Activate automatic address checking on the checkout', 'planzer'),
          'default' => 'no',
        ])
      ->endGroup('address_verification')

      ->startGroup(__('Manual transmission', 'planzer'), 'manual_orders', [
        'desc' => __('By default, orders with the status "Processing" are automatically transmitted to Planzer. Here you have the possibility to deactivate this function and send orders to Planzer manually by changing the status of the order to "Transmit to Planzer" manually.', 'planzer')
      ])
        ->addCheckbox(__('Manual transmission', 'planzer'), 'enable_manual_orders', [
          'desc' => __('Send orders to Planzer only after manual status adjustment', 'planzer'),
        ])
      ->endGroup('manual_orders')

      ->startGroup(__('Pickup Orders', 'planzer'), 'pickup_time', [
        'desc' => __('Orders until this time will get picked up by Planzer the same day', 'planzer')
      ])
        ->addTimeInput(__('Orders until', 'planzer'), 'pickup_time_today', [
          'required' => true,
        ])
      ->endGroup('pickup_time')
      ;
  }

  public function getId(): string
  {
    return 'sender';
  }

  public function getLabel(): string
  {
    return __('Sender', 'planzer');
  }

  private function getLanguages(): array
  {
    return [
      'de' => _x('Deutsch', 'language name', 'planzer'),
      'fr' => _x('Français', 'language name', 'planzer'),
      'it' => _x('Italiano', 'language name', 'planzer'),
      'en' => _x('English', 'language name', 'planzer'),
    ];
  }

  /**
   * @filter pre_update_option_planzer_sender_country
   */
  public function filterCountryOnSave(string $value, string $old_value, string $option): string
  {
    return explode(':', $value, 2)[0];
  }
}
