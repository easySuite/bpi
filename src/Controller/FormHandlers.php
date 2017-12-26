<?php

namespace Drupal\bpi\Controller;

use Bpi\Sdk\Exception\SDKException;
use Drupal\bpi\Bpi\BpiCore;
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

    /** @var \Drupal\bpi\Services\BpiService $bpi_service */
    $bpi_service = \Drupal::service('bpi.service');
    try {
      $push_result = $bpi_service->getInstance()
        ->push($bpi_content)
        ->getProperties();
      if (!empty($push_result['id'])) {
        $data['bid'] = $push_result['id'];
        bpi_update_syndicated($node->id(), BpiCore::BPI_PUSHED, $push_result['id'], $data);
        drupal_set_message(t('Node %title was successfully pushed to BPI well.', ['%title' => $node->getTitle()]));
      }
      else {
        drupal_set_message(t('Error when pushing content to BPI.'), 'error');
        \Drupal::logger('bpi')
          ->error(t('An error occurred when pushing content to BPI.'));
        return FALSE;
      }
    } catch (SDKException $e) {
      bpi_error_message($e->getCode());
      \Drupal::logger('bpi')->error($e->getMessage());
    }
  }

  /**
   * Custom validate handler for the configured content type that has
   * to be pushed during node save.
   *
   * Validates the audience and category fields.
   */
  public static function bpi_push_validate(&$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('send_to_bpi'))) {
      return;
    }

    $category = $form_state->getValue('bpi_push_category');
    if (empty($category)) {
      $form_state->setErrorByName('bpi_push_category', t('Category must be set when pushing to BPI.'));
    }

    $audience = $form_state->getValue('bpi_push_audience');
    if (empty($audience)) {
      $form_state->setErrorByName('bpi_push_audience', t('Audience must be set when pushing to BPI.'));
    }
  }
}
