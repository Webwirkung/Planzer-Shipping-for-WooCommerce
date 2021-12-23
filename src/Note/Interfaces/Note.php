<?php

namespace Planzer\Note\Interfaces;

interface Note
{
  public function generatePDF(string $path = '/tmp/'): string;

  public function getType(): string;

  public function getReferenceNumber(): string;
}
