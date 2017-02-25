<?php

/**
 * @file
 * Functionality to validate edit form elements.
 */

/**
 * Get the required fields.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @return array
 *   Associative array names of required fields. The field names are used as
 *   array key, the value may be an array of "OR" group names. At least one
 *   field of such a group must be available.
 */
// @todo Move this to field configuration to make required paragraphs generic.
function _editor_get_required_paragraphs(\Drupal\Core\Form\FormStateInterface $form_state) {

  /** @var \Drupal\node\NodeForm $node_form */
  $node_form = $form_state->getFormObject();
  $entity = $node_form->getEntity();
  $bundle = $entity->bundle();

  $required_paragraphs = [];

  //$configs = $this->config('settings.settings');
  $configs = \Drupal::config('settings.settings')->get($bundle);

  if (!empty($configs)) {
    foreach ($configs as $paragraph_name => $enabled) {
      if (1 == $enabled) {
        $required_paragraphs[$paragraph_name] = [];
      }
    }
   }

  return $required_paragraphs;
}

/**
 * Get the required paragraphs that the user did not added.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @param $field_name
 *   The name of the field that contains paragraphs.
 *
 * @return array
 *   Array of missing paragraphs machine names.
 */
function editor_get_missing_paragraphs(\Drupal\Core\Form\FormStateInterface $form_state, $field_name) {

  $required_paragraphs = _editor_get_required_paragraphs($form_state);
  $paragraphs_added = _editor_get_added_paragraphs($form_state, $field_name);

  if (empty($required_paragraphs) || empty($paragraphs_added)) {
    return [];
  }

  // Compute or get the required paragraphs that are not yet added.
  $missing_paragraphs = array_diff(array_keys($required_paragraphs), $paragraphs_added);
  $missing_paragraphs = _editor_filter_optional_required_paragraphs_or_groups($missing_paragraphs, $form_state, $field_name);

  return $missing_paragraphs;
}

/**
 * Get the required paragraph "OR" groups that contain required paragraphs.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @return array
 *   A list of required paragraph groups.
 */
function _editor_get_required_paragraph_or_groups(\Drupal\Core\Form\FormStateInterface $form_state) {
  $paragraph_groups = [];

  $required_paragraphs = _editor_get_required_paragraphs($form_state);
  if (empty($required_paragraphs)) {
    return [];
  }

  foreach ($required_paragraphs as $key => $attributes) {
    if (!empty($attributes['or_group'])) {
      $paragraph_groups[$attributes['or_group']][] = $key;
    }
  }

  return $paragraph_groups;
}

/**
 * Filter out optional required paragraphs.
 *
 * @param $missing_paragraphs
 *   The paragraphs that are required but not added by the user.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @param $field_name
 *   The field name housing the paragraphs.
 *
 * @return array
 *   Modified list of required paragraphs.
 */
function _editor_filter_optional_required_paragraphs_or_groups($missing_paragraphs, \Drupal\Core\Form\FormStateInterface $form_state, $field_name) {
  $required_paragraphs = _editor_get_required_paragraphs($form_state);
  $paragraph_groups = _editor_get_required_paragraph_or_groups($form_state);
  $added_paragraphs = _editor_get_added_paragraphs($form_state, $field_name);

  if (!empty($paragraph_groups)) {
    foreach ($missing_paragraphs as $missing_item) {
      if (!empty($required_paragraphs[$missing_item]['or_group'])) {
        $group = $required_paragraphs[$missing_item]['or_group'];

        $missing = array_diff($paragraph_groups[$group], $added_paragraphs);

        // Remove optional paragraphs.
        if (count($paragraph_groups[$group]) <> count($missing)) {
          foreach ($missing as $value) {
            if (($key = array_search($value, $missing_paragraphs)) !== FALSE) {
              unset($missing_paragraphs[$key]);
            }
          }
        }
      }
    }
  }

  return $missing_paragraphs;
}

/**
 * Get user added paragraphs.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state object.
 *
 * @param $field_name
 *   The field name housing the paragraphs.
 *
 * @return array
 *   Array of paragraph machine names.
 */
function _editor_get_added_paragraphs(\Drupal\Core\Form\FormStateInterface $form_state, $field_name) {

  $added_paragraphs = [];

  // Get the current state of the widget.
  $widget_state = \Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget::getWidgetState([], $field_name, $form_state);

  // Return early if the user did not add any paragraph.
  if (empty($widget_state['paragraphs'])) {
    return [];
  }

  // Build the list of added paragraphs.
  foreach ($widget_state['paragraphs'] as $data) {
    $added_paragraphs[] = $data['entity']->getType();
  }

  return $added_paragraphs;
}
