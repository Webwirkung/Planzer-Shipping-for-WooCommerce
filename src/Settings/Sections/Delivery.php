<?php

namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class Delivery extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup(__('Delivery', 'planzer'), 'delivery')
        ->addRadio(__('Delivery note', 'planzer'), 'generate_note', [
          'options' => [
            'delivery_note' => __('Generate delivery note', 'planzer'),
            'label_note' => __('Generate label note', 'planzer')
          ],
          'default' => 'label_note'
        ])
        ->addUrlInput(__('Logo URL', 'planzer'), 'note_logo', ['desc' => __('URL of logo that will be placed on the delivery note', 'planzer')])
        ->addTextInput(__('Note receiver', 'planzer'), 'note_email', [
          'desc' => __('Receiver of the delivery/label note (for multiple addresses separate them by using commas)', 'planzer'),
          'required' => true,
        ])
        ->addTextInput(__('Contact name', 'planzer'), 'note_contact_name', ['desc' => __('First and last name that will be placed on the delivery note', 'planzer')])
      ->endGroup('delivery');
  }

  public function getId(): string
  {
    return 'delivery';
  }

  public function getLabel(): string
  {
    return __('Delivery', 'planzer');
  }
}
