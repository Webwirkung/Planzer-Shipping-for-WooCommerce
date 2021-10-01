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
        ->addCheckbox(__('Delivery note', 'planzer'), 'generate_note', ['desc' => __('Generate delivery note', 'planzer')])
        ->addUrlInput(__('Logo URL', 'planzer'), 'note_logo', ['desc' => __('URL of logo that will be placed on the delivery note', 'planzer')])
        ->addEmailInput(__('Note receiver', 'planzer'), 'note_email', [
          'desc' => __('Receiver of the delivery note', 'planzer'),
          'required' => true,
        ])
        ->addTextInput(__('Contact name', 'planzer'), 'note_contact_name', ['desc' => __('First and last name that will be placed on the deliviery note')])
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
