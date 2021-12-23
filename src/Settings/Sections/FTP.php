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
        ->addToggle(__('Test Mode', 'planzer'), 'test_mode', [
          'desc' => __('Enable test mode of SFTP integration', 'planzer'),
          'default' => 'yes',
        ])
      ->endGroup('mode')

      ->startGroup(__('Live', 'planzer'), 'live')
        ->addTextInput(__('Account ID', 'planzer'), 'live_account_id', ['required' => true,])
        ->addTextInput(
            __('Server URL', 'planzer'),
            'live_server_address',
            [
              'desc' => __('Please do not change the URL unless you have been expressly requested to do so by support or you know what you are doing. Changes in this field can result in data no longer being transmitted to Planzer.', 'planzer'),
              'default' => 'lobplalb02.planzer.ch',
              'required' => true,
            ],
        )
      ->endGroup('live')
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
