<?php

namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class Notifications extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup(__('Notifications', 'planzer'), 'notifications')
        ->addRadio(__('Notifications', 'planzer'), 'sender_email_notifications', [
          'options' => [
            'yes' => __('Yes, send email notifications to me', 'planzer'),
            'no' => __('No, do not send email notifications to me', 'planzer'),
          ],
          'desc' => __('Do you want to receive notifications?', 'planzer'),
          'default' => 'yes',
        ])
        ->addCheckbox(__('Customer email notification', 'planzer'), 'receiver_email_notification', [
          'desc' => __('Send email notifications to the package receiver', 'planzer'),
          'default' => 'yes',
        ])
        ->addCheckbox(__('Customer SMS notification', 'planzer'), 'receiver_sms_notification', [
          'desc' => __('Send SMS notifications to the package receiver', 'planzer'),
          'default' => 'yes',
        ])
        ->addRadio(__('Notice of deposit', 'planzer'), 'deposit_notice', [
          'options' => [
            '1' => __('No deposit with signature', 'planzer'),
            '2' => __('Always deposit the package (without signature)', 'planzer'),
            '3' => __('Usually with signature but the receiver can choose if he wants the package to be deposited', 'planzer'),
          ],
          'default' => 2,
        ])
        ->addTextInput(__('Deposit notice', 'planzer'), 'deposit_notice_information')
      ->endGroup('notifications');
  }

  public function getId(): string
  {
    return 'notifications';
  }

  public function getLabel(): string
  {
    return __('Notifications', 'planzer');
  }
}
