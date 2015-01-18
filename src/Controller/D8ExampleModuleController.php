<?php

namespace Drupal\d8_example_module\Controller;

use Drupal\Core\Controller\ControllerBase;

class D8ExampleModuleController extends ControllerBase {

  public function test_page($from, $to) {
    $message = $this->t('%from to %to', [
      '%from' => $from,
      '%to' => $to,
    ]);

    return ['#markup' => $message];
  }

  public function test_page_with_theme($from, $to) {
    return [
      '#theme' => 'd8_example_module_page_theme',
      '#from' => $from,
      '#to' => $to,
    ];
  }
}
