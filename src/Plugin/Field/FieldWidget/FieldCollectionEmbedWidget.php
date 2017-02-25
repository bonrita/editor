<?php

namespace Drupal\editor\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Render\Element;
use Drupal\field_collection\Entity\FieldCollectionItem;
use Drupal\field_collection\Plugin\Field\FieldWidget\FieldCollectionEmbedWidget as ContribFieldCollectionEmbedWidget;

/**
 * Plugin implementation of the 'field_collection_embed_min_max' widget.
 *
 * @FieldWidget(
 *   id = "field_collection_embed_min_max",
 *   label = @Translation("Embedded min max"),
 *   field_types = {
 *     "field_collection"
 *   },
 * )
 */
class FieldCollectionEmbedWidget extends ContribFieldCollectionEmbedWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'min' => NULL,
      'max' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * @inheritDoc
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#element_validate'][] = [$this, 'validate_min_max'];
    return $element;
  }

  /**
   * Validate the minimum and maximum number of added items.
   *
   * @param $element
   *   The structure of the element.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @param array $form
   *   The structure of the form.
   */
  public function validate_min_max($element, FormStateInterface $form_state, $form) {
    $field_parents = $element['#field_parents'];
    $field_name = $element['#field_name'];
    $triggering_element = $form_state->getTriggeringElement();
    $min = $this->getSetting('min');
    $max = $this->getSetting('max');

    $field_state = static::getWidgetState($field_parents, $field_name, $form_state);
    $count = count($field_state['entity']);

    $field_parent_name = implode('][', $field_parents);
    if (empty($triggering_element['#published_status'])) {
      // @todo Fix the problem. The error messages are almost never rendered.
      // Validate when the add more button is clicked.
      if ($count == $max || $count > $max) {
        $form_state->setErrorByName("{$field_parent_name}][{$field_name}][add_more", $this->t('The maximum number of @number items in @field_name has been reached.', ['@number' => $max, '@field_name' => $this->fieldDefinition->getLabel()]));
      }
    }
    else {
      // Validate when publishing the node.
      if ($count > $max) {
        $form_state->setErrorByName("{$field_parent_name}][{$field_name}][add_more", $this->t('You can only add @number items to @field_name.', ['@number' => $max, '@field_name' => $this->fieldDefinition->getLabel()]));
      }
      elseif ($count < $min) {
        $form_state->setErrorByName("{$field_parent_name}][{$field_name}][add_more", $this->t('You must add at least @number items to @field_name.', ['@number' => $min, '@field_name' => $this->fieldDefinition->getLabel()]));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['min'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum'),
      '#description' => $this->t('The minimum number of field collection items to be added.'),
      '#default_value' => $this->getSetting('min'),
    ];

    $element['max'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum'),
      '#description' => $this->t('The maximum number of field collection items to be added.'),
      '#default_value' => $this->getSetting('max'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $preview = [];

    $min = $this->getSetting('min');
    $max = $this->getSetting('max');

    if (isset($max)) {
      $preview[] = $this->t('A maximum of : @max field collection items can be added', ['@max' => $max]);
    }

    if (!empty($min)) {
      $preview[] = $this->t('A minimum of : @min field collection items can be added', ['@min' => $min]);
    }

    return $preview;
  }

}
