<?php

namespace Drupal\editor\Plugin\EntityReferenceSelection;

use Drupal\views\Plugin\EntityReferenceSelection\ViewsSelection as CoreViewsSelection;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * This plugin takes into account the configured fields in the view
 * so that the titles are rendered as configured in the view.
 *
 * @EntityReferenceSelection(
 *   id = "views_ch",
 *   label = @Translation("Ch views: Filter by an entity reference view"),
 *   group = "views_custom",
 *   weight = 0
 * )
 */
class ViewsSelection extends CoreViewsSelection {

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {

    $handler_settings = $this->configuration['handler_settings'];
    $display_name = $handler_settings['view']['display_name'];
    $arguments = $handler_settings['view']['arguments'];
    $result = array();
    if ($this->initializeView($match, $match_operator, $limit)) {
      // Get the results.
      $result = $this->view->executeDisplay($display_name, $arguments);
    }

    $return = array();
    if ($result) {
      // Get the search field.
      $search_fields = array_keys($this->view->style_plugin->options['search_fields']);
      $search_field = reset($search_fields);

      foreach ($this->view->result as $row) {
        $entity = $row->_entity;
        $rendered_field = $this->view->style_plugin->getField($row->index, $search_field);
        $return[$entity->bundle()][$entity->id()] = $rendered_field;
      }
    }

    return $return;
  }

}
