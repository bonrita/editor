<?php

namespace Drupal\editor\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function AlterRoutes(RouteCollection $collection) {
    // Override access for better Editor experience.

    // Structure menu item '/admin/structure'.
    if ($route = $collection->get('system.admin_structure')) {
      $route->setRequirement('_permission', 'editor access menu items');
    }
  }

}
