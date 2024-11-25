<?php

namespace App\Services;

class TemplateRenderer
{
  public static function render($content, array $data)
  {
    foreach ($data as $key => $value) {
      $content = str_replace("{{{$key}}}", $value, $content);
    }

    return $content;
  }

}
