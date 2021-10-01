<?php
namespace Planzer\Settings\Sections;

abstract class SectionBase
{
  private array $settings = [];

  protected function addTextInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, []));
  }

  protected function addNumberInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'number',
      'custom_attributes' => [
        'min' => 0,
        'step' => 1,
      ],
    ]));
  }

  protected function addEmailInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'email',
    ]));
  }

  protected function addPasswordInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'password',
    ]));
  }

  protected function addUrlInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'url',
    ]));
  }

  protected function addTimeInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'time',
    ]));
  }

  protected function addSelectInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'select',
      'options' => [
        'min'  => 0,
        'step' => 1,
      ],
    ]));
  }

  protected function addMultiselectInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'multiselect',
      'options' => [
        '0' => 0,
        '1' => 1,
        '2' => 2,
      ],
    ]));
  }

  protected function addCheckbox(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'checkbox',
      'default' => 'no'
    ]));
  }

  protected function addRadio(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'radio',
      'options' => [
        'yes' => __('Yes', 'planzer'),
        'no' => __('No', 'planzer'),
      ],
    ]));
  }

  protected function addCountryInput(string $title, string $id, array $args = []): self
  {
    return $this->addField($title, $id, wp_parse_args($args, [
      'type' => 'single_select_country',
    ]));
  }

  protected function addField(string $title, string $id, array $args = []): self
  {
    $args = wp_parse_args($args, [
      'title' => $title,
      'desc' => '',
      'id' => "planzer_{$this->getId()}_{$id}",
      'type' => 'text',
      'default' => '',
      'css' => '',
      'autoload' => false,
      'desc_tip' => false,
      'class' => '',
    ]);

    if (! empty($args['required'])) {
      unset($args['required']);
      $args['title'] .= '*';
      $args['custom_attributes']['required'] = '';
    }

    if (! empty($args['readonly'])) {
      unset($args['readonly']);
      $args['custom_attributes']['readonly'] = 'readonly';
    }

    if (! empty($args['disabled'])) {
      unset($args['disabled']);
      $args['custom_attributes']['disabled'] = 'disabled';
    }

    $this->settings[] = $args;
    return $this;
  }

  protected function addTitle(string $title, array $args = []): self
  {
    $this->settings[] = wp_parse_args($args, [
      'title' => $title,
      'type' => 'title',
      'desc' => ''
    ]);
    return $this;
  }

  protected function startGroup(string $title, string $id = '', array $args = []): self
  {
    $this->settings[] = wp_parse_args($args, [
      'title' => $title,
      'type' => 'title',
      'desc' => '',
      'id' => "planzer_{$this->getId()}_" . sanitize_title(! empty($id) ? $id : $title),
    ]);
    return $this;
  }

  protected function endGroup(string $id, array $args = []): self
  {
    $this->settings[] = wp_parse_args($args, [
      'type' => 'sectionend',
      'id' => "planzer_{$this->getId()}_" . sanitize_title($id),
    ]);
    return $this;
  }

  public function getId(): string
  {
    return 'general';
  }

  public function getSettings(): array
  {
    return $this->settings;
  }
}
