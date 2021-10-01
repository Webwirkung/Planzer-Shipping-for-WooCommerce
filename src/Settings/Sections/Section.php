<?php
namespace Planzer\Settings\Sections;

interface Section
{
  public function getLabel(): string;

  public function getId(): string;

  public function getSettings(): array;
}
