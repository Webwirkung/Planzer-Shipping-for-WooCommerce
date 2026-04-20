<?php

namespace Planzer\CSV;

use DateTimeImmutable;
use DateTimeZone;
use Planzer\Helpers\Address as AddressHelper;
use Planzer\Package\Package as PlanzerPackage;
use WC_Order;

class DataLoader
{
    private ?array $field_mapping = null;
    private string $time_zone = 'Europe/Zurich';
    private string $pickup_end_of_day_hour = '15:00';
    private string $pickup_pickup_from = '16:00';
    private string $pickup_pickup_to = '18:00';
    private WC_Order $order;
    private PlanzerPackage $package;

    public function __construct(WC_Order $order, PlanzerPackage &$package)
    {
        $this->time_zone = apply_filters('plz/csv/data/time_zone', 'Europe/Zurich');
        $this->pickup_end_of_day_hour = apply_filters(
            'plz/csv/data/pickup/end_of_day',
            get_option('planzer_sender_pickup_time_today', '15:00')
        );
        $this->pickup_pickup_from = apply_filters('plz/csv/data/pickup/from', $this->pickup_pickup_from);
        $this->pickup_pickup_to = apply_filters('plz/csv/data/pickup/to', $this->pickup_pickup_to);
        $this->order = $order;
        $this->package = $package;
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
            return (string) $value;
        }

        return '';
    }

    private function initFieldMapping(): void
    {
        $parsed_address = AddressHelper::getParsedAddressLine(
            $this->order->get_shipping_address_1() ?: $this->order->get_billing_address_1()
        );

        $this->field_mapping = [
            'A1_0'  => 'A1',
            'A1_1'  => $this->getAccountNumber(),
            'A1_2'  => 'planzer_sender_customer_number',
            'A1_3'  => 'planzer_sender_department_number',
            'A1_4'  => 'G',
            'A1_8'  => 'planzer_sender_company_name',
            'A1_9'  => 'planzer_sender_company_extra',

            'A1_10' => 'planzer_sender_country',
            'A1_11' => 'planzer_sender_city',
            'A1_12' => 'planzer_sender_zip',
            'A1_13' => 'planzer_sender_street',
            'A1_14' => 'planzer_sender_house_number',
            'A1_17' => 'planzer_sender_instruction',
            'A1_18' => 'planzer_sender_phone',
            'A1_19' => 'planzer_sender_mobile',

            'A1_20' => $this->getSenderSmsNotificationValue(),
            'A1_21' => 'planzer_sender_email',
            'A1_22' => $this->getSenderEmailNotificationValue(),
            'A1_23' => 'planzer_sender_language',

            'A1_34' => $this->getPickupDate()->format('d.m.Y'),
            // 'A1_35' => implode('.', [...explode(':', $this->pickup_pickup_from), '00']),
            // 'A1_36' => implode('.', [...explode(':', $this->pickup_pickup_to), '00']),
            'A1_39' => $this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name(),

            'A1_40' => $this->order->get_shipping_last_name() ?: $this->order->get_shipping_first_name(),
            'A1_41' => AddressHelper::isShippingAddressFiled($this->order)
                ? $this->order->get_shipping_company()
                : $this->order->get_billing_company(),
            'A1_43' => $this->order->get_shipping_country() ?: $this->order->get_billing_country(),
            'A1_44' => $this->order->get_shipping_city() ?: $this->order->get_billing_city(),
            'A1_45' => $this->order->get_shipping_postcode() ?: $this->order->get_billing_postcode(),
            'A1_46' => $parsed_address['street'],
            'A1_47' => $parsed_address['house_number'],

            'A1_51' => AddressHelper::isShippingAddressFiled($this->order) ? '' : $this->order->get_billing_phone(),
            'A1_53' => $this->getReceiverSmsNotificationValue(),
            'A1_54' => AddressHelper::isShippingAddressFiled($this->order) ? '' : $this->order->get_billing_email(),
            'A1_55' => $this->getReceiverEmailNotificationValue(),
            'A1_56' => 'planzer_sender_language',
            'A1_57' => apply_filters('planzer/csv/client_salutation', '', $this->order),

            'A1_67' => $this->getDeliveryDate()->format('d.m.Y'),

            'A1_70' => "{$this->order->get_id()}_{$this->package->getSequenceNumber(0)}",
            'A1_73' => get_option('planzer_delivery_delivery_time_chargable', ''),
            'A1_76' => $this->getDepositNoticeInformation(),

            'A1_82' => $this->getNotificationValueFor('A1_82'),
        ];

        $this->field_mapping = array_merge($this->field_mapping, [
            'P1_0' => 'P1',
            'P1_1' => 'PAKE',
            'P1_7' => "{$this->order->get_id()}_{$this->package->getSequenceNumber(0)}",
            'P1_8' => $this->package->getQRContentWithoutSuffix(),
        ]);

        $this->field_mapping = array_merge($this->field_mapping, [
            'O1_0' => 'O1',
            'O1_1' => $this->getNotificationValueFor('O1_1'),
        ]);
    }

    private function getDepositNoticeInformation(): string
    {
        return ('2' === get_option('planzer_notifications_deposit_notice'))
            ? (string) get_option('planzer_notifications_deposit_notice_information')
            : '';
    }

    private function getAccountNumber(): string
    {
        return (string) get_option('planzer_ftp_live_account_id');
    }

    private function getReceiverSmsNotificationValue(): string
    {
        return 'yes' === get_option('planzer_notifications_receiver_sms_notification', 'no') ? '' : 'A';
    }

    private function getReceiverEmailNotificationValue(): string
    {
        return 'yes' === get_option('planzer_notifications_receiver_email_notification', 'no') ? '' : 'B';
    }

    private function getSenderSmsNotificationValue(): string
    {
        return 'A';
    }

    private function getSenderEmailNotificationValue(): string
    {
        return 'yes' === get_option('planzer_notifications_sender_email_notifications', 'no') ? '' : 'B';
    }

    public function getPickupDate(): DateTimeImmutable
    {
        $tz = new DateTimeZone($this->time_zone);

        $pickup_date = new DateTimeImmutable('now', $tz);

        [$hour, $minute] = $this->parseHourMinute($this->pickup_end_of_day_hour);
        $end_of_day_date = $pickup_date->setTime($hour, $minute);

        $is_after_or_equal_eod = $pickup_date->getTimestamp() >= $end_of_day_date->getTimestamp();
        $is_weekend = $this->isWeekend($pickup_date);

        if ($is_after_or_equal_eod || $is_weekend) {
            $pickup_date = $this->maybeMoveToNextWorkingDay($pickup_date->modify('+1 day'));
        }

        return apply_filters('planzer/csv/pickup_date', $pickup_date, $this->order);
    }

    public function getDeliveryDate(): DateTimeImmutable
    {
        $delivery_date = $this->maybeMoveToNextWorkingDay(
            $this->getPickupDate()->modify('+1 day'),
            'yes' === get_option('planzer_delivery_saturday_delivery')
        );

        return apply_filters('planzer/csv/delivery_date', $delivery_date, $this->order);
    }

    private function maybeMoveToNextWorkingDay(DateTimeImmutable $date, bool $checkWeekends = false): DateTimeImmutable
    {
        [$hour, $minute] = $this->parseHourMinute($this->pickup_pickup_from);

        while ($checkWeekends ? $this->isSunday($date) : $this->isWeekend($date)) {
            $date = $date
                ->modify('+1 day')
                ->setTime($hour, $minute);
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
                    case 'O1_1':
                        return 'Depot';
                    default:
                        return '';
                }

            case '2':
                switch ($field) {
                    case 'A1_82':
                        return $this->getDepositNoticeInformation();
                    case 'O1_1':
                        return '';
                    default:
                        return '';
                }

            default:
                return '';
        }
    }

    private function parseHourMinute(string $time): array
    {
        [$hour, $minute] = explode(':', $time);

        return [(int) $hour, (int) $minute];
    }

    private function isWeekend(DateTimeImmutable $date): bool
    {
        return (int) $date->format('N') >= 6;
    }

    private function isSunday(DateTimeImmutable $date): bool
    {
        return (int) $date->format('N') === 7;
    }
}
