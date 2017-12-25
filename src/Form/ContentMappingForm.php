<?php

namespace Drupal\bpi\Form;

use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ContentMappingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bpi_content_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'bpi.content_mapping',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node_types = node_type_get_names();
    $node_types_names = array_keys($node_types);

    $settings = $this->config('bpi.content_mapping');

    $form['bpi_content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#description' => $this->t('Select a content type into which content from BPI will be syndicated.'),
      '#options' => $node_types,
      '#default_value' => $settings->get('bpi_content_type') ?: reset($node_types_names),
      '#ajax' => [
        'callback' => 'Drupal\bpi\Form\ContentMappingForm::_mapping_callback',
        'wrapper' => 'bpi-field-mapper-wrapper',
        'effect' => 'fade',
        'method' => 'replace',
      ],
    ];

    $default = $form['bpi_content_type']['#default_value'];
    $selected_node_type = $form_state->getValue('bpi_content_type', $default);
    $field_instances = $this->getFieldInstances($selected_node_type);

    $form['bpi_mapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Field mapping'),
      '#description' => $this->t('Define you custom mapping rules. Each dropdown maps BPI related fields to your content fields.'),
      '#prefix' => '<div id="bpi-field-mapper-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['bpi_mapper']['bpi_field_title'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI title'),
      '#description' => $this->t('Titles are automatically assigned.'),
      '#options' => ['title' => $this->t('Title')],
      '#default_value' => $settings->get('bpi_field_title') ?: 'title',
      '#disabled' => TRUE,
    ];

    $form['bpi_mapper']['bpi_field_teaser'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI teaser'),
      '#description' => $this->t('The field to extract the teaser from. If the content type have a body summary, assign it to the body field.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_teaser') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_body'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI body'),
      '#description' => $this->t('Field to extract the main content from (body field).'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_body') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_tags'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI tags'),
      '#description' => $this->t('Field used to get tags from.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_tags') ?: '',
    ];

    $form['bpi_mapper']['bpi_field_materials'] = [
      '#type' => 'select',
      '#title' => $this->t('BPI materials'),
      '#description' => $this->t('Field used to get reference to the T!NG data well.'),
      '#options' => $field_instances,
      '#default_value' => $settings->get('bpi_field_materials') ?: '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Custom ajax callback.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function _mapping_callback(array &$form, FormStateInterface $form_state) {
    return $form['bpi_mapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bpi.content_mapping')
      ->set('bpi_content_type', $form_state->getValue('bpi_content_type'))
      ->set('bpi_field_teaser', $form_state->getValue('bpi_field_teaser'))
      ->set('bpi_field_body', $form_state->getValue('bpi_field_body'))
      ->set('bpi_field_tags', $form_state->getValue('bpi_field_tags'))
      ->set('bpi_field_materials', $form_state->getValue('bpi_field_materials'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get a list of fields, for a certain node type.
   *
   * Simplifies and filters the output of the core field_info_instances()
   * function.
   *
   * Filtering means that we do not want text values into image fields, etc.
   *
   * @param string $node_type
   *   Node type machine name, whose fields list is expected.
   *
   * @return array
   *   An array with the fields, for the specified node type.
   */
  public function getFieldInstances($node_type) {
    if (empty($node_type)) {
      return [];
    }

    $entityManager = \Drupal::service('entity.manager');
    $node_fields = array_filter($entityManager->getFieldDefinitions('node', $node_type), function ($field_definition) {
      return $field_definition instanceof FieldConfigInterface;
    });

    $allowed_types = [
      'text_long',
      'text_with_summary',
      'string',
      'entity_reference',
    ];

    $fields = [];
    /** @var \Drupal\field\Entity\FieldConfig $field */
    foreach ($node_fields as $field) {
      if (in_array($field->getType(), $allowed_types)) {
        $fields[$field->getName()] = $field->getLabel();
      }
    }

    return $fields;
  }
}
