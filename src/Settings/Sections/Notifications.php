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
        ->addRadio(__('Notifications', 'planzer'), 'notifications_enabled', ['options' => [
          'yes' => __('Yes, send notifications', 'planzer'),
          'no' => __('No, do not send notifications', 'planzer'),
        ],])
        ->addCheckbox(__('Email notification', 'planzer'), 'email_notification', [
          'desc' => __('Send email notifications', 'planzer'),
          'required' => true,
        ])
        ->addCheckbox(__('SMS notification', 'planzer'), 'sms_notification', ['desc' => __('Send sms notifications', 'planzer')])
        ->addRadio(__('Notice of deposit', 'planzer'), 'deposit_notice', ['options' => [
          '1' => __('No deposit with signature', 'planzer'),
          '2' => __('Always deposit the package (without signature)', 'planzer'),
          '3' => __('Usually with signature but the receiver can choose if he wants the package to be deposited', 'planzer'),
        ],])
        ->addTextInput(__('Content of package', 'planzer'), 'package_content')
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
