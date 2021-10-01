<?php

namespace Planzer\CSV;

use Carbon\Carbon;
use Planzer\Package\Package as PlanzerPackage;
use Planzer\Helpers\Address as AddressHelper;
use WC_Order;

class DataLoader
{
  private ?array $field_mapping = null;
  private string $time_zone = 'Europe/Zurich';
  private string $pickup_end_of_day_hour = '15:00';
  private string $pickup_pickup_from = '08:00';
  private string $pickup_pickup_to = '16:00';
  private ?WC_Order $order = null;

  public function __construct(WC_Order $order)
  {
    $this->time_zone = apply_filters('plz/csv/data/time_zone', 'Europe/Zurich');
    $this->pickup_end_of_day_hour = apply_filters('plz/csv/data/pickup/end_of_day', get_option('planzer_sender_pickup_time_today', '15:00'));
    $this->pickup_pickup_from = apply_filters('plz/csv/data/pickup/from', get_option('planzer_sender_pickup_time_from', '08:00'));
    $this->pickup_pickup_to = apply_filters('plz/csv/data/pickup/to', get_option('planzer_sender_pickup_time_until', '16:00'));
    $this->order = $order;
    $this->initFieldMapping();
  }

  public function getGroupFieldData(string $group, int $field): string
  {
    if ($group === '') {
      return '';
    }

    $field_no = $group . '1_' . $field;
    if (array_key_exists($field_no, $this->field_mapping)) {
      $value = $this->field_mapping[$field_no];
      if (0 === strpos($value, 'planzer_')) {
        $value = get_option($value);
      }
      return $value;
    }
    return '';
  }

  private function initFieldMapping(): void
  {
    $package = new PlanzerPackage($this->order->get_id());
    $parsed_address = AddressHelper::getParsedAddressLine($this->order->get_shipping_address_1() ?: $this->order->get_billing_address_1());
    $this->field_mapping = [
      // 'CSV_fiels_number' => 'setting_name_or_val', //field names start with "planzer_"
      'A1_0' => 'A1',
      'A1_1' => $this->getAccountNumber(),
      'A1_2' => 'planzer_sender_customer_number',
      'A1_3' => 'planzer_sender_department_number',
      'A1_4' => 'G',
      'A1_8' => 'planzer_sender_company_name',
      'A1_9' => 'planzer_sender_company_extra',

      'A1_10' => 'planzer_sender_country',
      'A1_11' => 'planzer_sender_city',
      'A1_12' => 'planzer_sender_zip',
      'A1_13' => 'planzer_sender_street',
      'A1_14' => 'planzer_sender_house_number',
      'A1_17' => 'planzer_sender_instruction',
      'A1_18' => 'planzer_sender_phone',
      'A1_19' => 'planzer_sender_mobile',

      'A1_20' => $this->getSmsNotificationValue(),
      'A1_21' => 'planzer_sender_email',
      'A1_22' => $this->getEmailNotificationValue(),
      'A1_23' => 'planzer_sender_language',

      'A1_34' => $this->getPickupDate()->format('d.m.Y'),
      'A1_35' => implode('.', [...explode(':', $this->pickup_pickup_from), '00']),
      'A1_36' => implode('.', [...explode(':', $this->pickup_pickup_to), '00']),
      'A1_39' => $this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name(),

      'A1_40' => $this->order->get_shipping_last_name() ?: $this->order->get_shipping_first_name(),
      'A1_41' => $this->order->get_shipping_company() ?: $this->order->get_billing_company(),
      'A1_43' => $this->order->get_shipping_country() ?: $this->order->get_billing_country(),// @TO-DO test values
      'A1_44' => $this->order->get_shipping_city() ?: $this->order->get_billing_city(),
      'A1_45' => $this->order->get_shipping_postcode() ?: $this->order->get_billing_postcode(),
      'A1_46' => $parsed_address['street'],
      'A1_47' => $parsed_address['house_number'],

      'A1_51' => $this->order->get_billing_phone(),
      'A1_53' => $this->getSmsNotificationValue(),
      'A1_54' => $this->order->get_billing_email(),
      'A1_55' => $this->getEmailNotificationValue(),
      'A1_56' => 'planzer_sender_language',
      'A1_57' => apply_filters('planzer/csv/client_salutation', '', $this->order),

      'A1_67' => $this->getDeliveryDate()->format('d.m.Y'),

      'A1_70' => $package->getPackageNumber(),

      // MS: do not use this field anymore - see PLZ-27
      // 'A1_76' => 'planzer_notifications_deposit_notice',

      'A1_82' => $this->getNotificationValueFor('A1_82'),
    ];

    $this->field_mapping = array_merge($this->field_mapping, [
      'P1_0' => 'P1',
      'P1_1' => 'PAKE',
      'P1_6' => 'planzer_notifications_package_content',
      'P1_7' => $package->getPackageNumber(),
      'P1_8' => $package->getQRContentWithoutSuffix(),
    ]);

    $this->field_mapping = array_merge($this->field_mapping, [
      'O1_0' => 'O1',
      'O1_1' => $this->getNotificationValueFor('O1_1'),
    ]);
  }

  private function getAccountNumber(): string
  {
    if ('no' !== get_option('planzer_ftm_test_mode', 'yes')) {
      return get_option('planzer_ftp_test_account_id');
    }
    return get_option('planzer_ftp_live_account_id');
  }

  private function getSmsNotificationValue(): string
  {
    if ('no' !== get_option('planzer_notifications_notifications_enabled', 'no')) {
      return 'yes' === get_option('planzer_notifications_sms_notification') ? '' : 'A';
    }
    return 'A';
  }

  private function getEmailNotificationValue(): string
  {
    if ('no' !== get_option('planzer_notifications_notifications_enabled', 'no')) {
      return 'yes' === get_option('planzer_notifications_email_notification') ? '' : 'B';
    }
    return 'B';
  }

  public function getPickupDate(): Carbon
  {
    $order_date = $this->order->get_date_paid();
    $pickup_date = Carbon::now($this->time_zone);
    if (! empty($order_date)) {
      $pickup_date = Carbon::createFromTimestamp($order_date->getTimestamp(), $this->time_zone);
    }

    if ($this->pickup_end_of_day_hour <= $pickup_date->hour) {
      //if is after "EOD" hour move pickup to next avaiable day
      $pickup_date = $this->maybeMoveToNextWorkingDay($pickup_date->addDay());
    }
    return apply_filters('planzer/csv/pickup_date', $pickup_date, $this->order);
  }

  public function getDeliveryDate(): Carbon
  {
    $delivery_date = $this->maybeMoveToNextWorkingDay($this->getPickupDate()->addDay());
    return apply_filters('planzer/csv/delivery_date', $delivery_date, $this->order);
  }

  private function maybeMoveToNextWorkingDay(Carbon $date): Carbon
  {
    while ($date->isWeekend()) {
      $date->addDay();
      $date->hour = explode(':', $this->pickup_pickup_from)[0];
      $date->minute = explode(':', $this->pickup_pickup_from)[1];
    }

    return $date;
  }

  private function getNotificationValueFor(string $field): string
  {
    switch (get_option('planzer_notifications_deposit_notice', 0)) {
      case '1':
        switch ($field) {
          case 'A1_82':
                return '';
                break;
          case 'O1_1':
                return 'Depot';
                break;
          default:
                return '';
                break;
        }
            break;
      case '2':
        switch ($field) {
          case 'A1_82':
                return 'Deponieren';
                break;
          case 'O1_1':
                return '';
                break;
          default:
                return '';
                break;
        }
            break;
      default:
            return '';
            break;
    }
  }
}
