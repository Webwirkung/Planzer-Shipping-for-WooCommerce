<?php

namespace Planzer\DeliveryNote;

class Template
{
  public static function generateTemplate(string $template_name, array $data): string
  {
    ob_start();

    include PLANZER_RESOURCES_PATH . "/views/$template_name.php";

    return ob_get_clean();
  }
}
