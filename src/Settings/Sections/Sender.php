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
        ->addNumberInput(__('Control No.', 'planzer'), 'control_number', [
          'custom_attributes' => [
            'min' => 0,
            'max' => 999,
            'step' => 1,
          ],
          'required' => true,
        ])
      ->endGroup('contract_details')

      ->startGroup(__('Sender Information', 'planzer'), 'sender_information')
        ->addTextInput(__('Company', 'planzer'), 'company_name', ['required' => true])
        ->addTextInput(__('Company extra', 'planzer'), 'company_extra')
      ->endGroup('sender_information')

      ->startGroup(__('Shop Default data', 'planzer'), 'shop_data')
        ->addTextInput(__('Street', 'planzer'), 'street', [
          'required' => true,
          'default' => get_option('woocommerce_store_address')
        ])
        ->addTextInput(__('House Number', 'planzer'), 'number', [
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

      ->startGroup(__('Pickup Orders', 'planzer'), 'pickup_time', [
        'desc' => __('Orders until this time will get picked up by Planzer the same day', 'planzer')
      ])
        ->addTimeInput(__('Orders until', 'planzer'), 'pickup_time_today', [
          'required' => true,
        ])
      ->endGroup('pickup_time')

      ->startGroup(__('Pickup Date', 'planzer'), 'pickup_date', [
        'desc' => __('Pickup date is order date + 1 day', 'planzer')
      ])
        ->addTimeInput(__('Time from', 'planzer'), 'pickup_time_from', [
          'required' => true,
        ])
        ->addTimeInput(__('Time until', 'planzer'), 'pickup_time_until', [
          'required' => true,
        ])
      ->endGroup('pickup_date')
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
