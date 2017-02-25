<?php

namespace Drupal\editor\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget as CoreImageWidget;

/**
 * Plugin implementation of the 'image_image' widget.
 *
 * @FieldWidget(
 *   id = "editor_image",
 *   label = @Translation("Image with caption"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageWidget extends CoreImageWidget {

  /**
   * @inheritDoc
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);
    $element['title']['#description'] = '';
    $element['title']['#title'] = t('Caption');
    return $element;
  }

}
