<?php

namespace Drupal\d8_example_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

    /**
     * Test Block.
     *
     * @Block(
     *   id = "test_block",
     *   admin_label = @Translation("Test Block"),
     *   category = @Translation("System")
     * )
     */
class TestBlock extends BlockBase {

  public function defaultConfiguration() {
    return ['enabled' => 1];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Configuration enabled'),
      '#default_value' => $this->configuration['enabled'],
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['enabled'] = (bool)$form_state->getValue('enabled');
  }

  public function build() {
    if ($this->configuration['enabled']) {
      $message = $this->t('Configuration enabled');
    }
    else {
      $message = $this->t('Configuration disabled');
    }
    return [
      '#markup' => $message,
    ];
  }
}
