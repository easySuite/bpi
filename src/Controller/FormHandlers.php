<?php

namespace Drupal\bpi\Controller;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class FormHandlers
 *
 * @package Drupal\bpi\Controller
 */
class FormHandlers {

  /**
   * Custom submit handler for the configured content type that has
   * to be pushed during node save.
   *
   * Catches the submit event of a form to push the node to bpi service.
   */
  public static function bpi_push_submit(&$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('send_to_bpi'))) {
      return;
    }

    $category = $form_state->getValue('bpi_push_category');
    $audience = $form_state->getValue('bpi_push_audience');
    $with_images = $form_state->getValue('bpi_push_images');
    $authorship = $form_state->getValue('bpi_push_ccl');
    $editable = $form_state->getValue('bpi_push_editable');

    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_state->getFormObject()->getEntity();

    $bpi_content = bpi_convert_to_bpi($node, $category, $audience, $with_images, $authorship, $editable);

    /** @var \Drupal\bpi\Services\BpiService $bpiService */
    $bpi_service = \Drupal::service('bpi.service');
    $push_result = $bpi_service->getInstance()->push($bpi_content)->getProperties();
  }
}
