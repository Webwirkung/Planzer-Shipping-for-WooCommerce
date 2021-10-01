<?php
namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class FTP extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup(__('Mode', 'planzer'), 'mode')
        ->addCheckbox(__('Test Mode'), 'test_mode', ['desc' => __('Enable test mode of SFTP integration', 'planzer')])
      ->endGroup('mode')

      ->startGroup(__('Test', 'planzer'), 'test')
        ->addTextInput(__('Account ID', 'planzer'), 'test_account_id')
        ->addTextInput(__('IP Address', 'planzer'), 'test_ip_address')
      ->endGroup('test')

      ->startGroup(__('Live', 'planzer'), 'live')
        ->addTextInput(__('Account ID', 'planzer'), 'live_account_id')
        ->addTextInput(__('IP Address', 'planzer'), 'live_ip_address')
      ->endGroup('live')

      ->startGroup(__('Account', 'planzer'), 'account')
        ->addTextInput(__('Username', 'planzer'), 'username', [
          'required' => true,
        ])
        ->addPasswordInput(__('Password', 'planzer'), 'password', [
          'required' => true,
        ])
      ->endGroup('account')
    ;
  }

  public function getId(): string
  {
    return 'ftp';
  }

  public function getLabel(): string
  {
    return __('FTP', 'planzer');
  }
}
