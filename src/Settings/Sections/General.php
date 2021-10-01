<?php
namespace Planzer\Settings\Sections;

use Planzer\Settings\Sections\Section;
use Planzer\Settings\Sections\SectionBase;

class General extends SectionBase implements Section
{
  public function __construct()
  {
    $this
      ->startGroup('General')
        ->addCheckbox(__('Checkbox', 'planzer'), 'checkboxid')
        ->addTextInput(__('Text', 'planzer'), 'textid')
        ->addNumberInput(__('Number', 'planzer'), 'numberid')
        ->addSelectInput(__('Select', 'planzer'), 'selectid')
      ->endGroup('General');
  }

  public function getId(): string
  {
    return 'general';
  }

  public function getLabel(): string
  {
    return __('General', 'planzer');
  }
}
