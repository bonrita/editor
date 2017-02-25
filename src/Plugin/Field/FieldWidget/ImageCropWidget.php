<?php

namespace Drupal\editor\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget as ContribImageCropWidget;

/**
 * Plugin implementation of the 'image_image' widget.
 *
 * @FieldWidget(
 *   id = "editor_crop_image",
 *   label = @Translation("Image crop with caption"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageCropWidget extends ContribImageCropWidget  {

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
