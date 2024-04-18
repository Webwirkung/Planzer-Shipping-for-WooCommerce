<?php

namespace Planzer\Note\Templates;

class Template
{
  public static function generateTemplate(string $template_name, array $data): string
  {
    ob_start();

    $default_template_path = PLANZER_RESOURCES_PATH . "/views/";
    $template_path = apply_filters('planzer_note_template_path', $default_template_path);
    include $template_path . $template_name . ".php";

    return ob_get_clean();
  }
}
