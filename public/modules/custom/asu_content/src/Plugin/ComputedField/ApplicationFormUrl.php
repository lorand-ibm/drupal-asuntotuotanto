<?php

namespace Drupal\asu_content\Plugin\ComputedField;

use Drupal\asu_content\Entity\Apartment;
use Drupal\computed_field_plugin\Traits\ComputedSingleItemTrait;
use Drupal\Core\Field\FieldItemList;

/**
 * Computed field ApplicationFormUrl.
 *
 * @ComputedField(
 *   id = "asu_application_form_url",
 *   label = @Translation("Application form url"),
 *   type = "asu_computed_render_array",
 *   entity_types = {"node"},
 *   bundles = {"apartment"}
 * )
 */
class ApplicationFormUrl extends FieldItemList {
  use ComputedSingleItemTrait;

  /**
   * Compute the street address value.
   *
   * @return mixed
   *   Returns the computed value.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function singleComputeValue() {
    /** @var Drupal\asu_content\Entity\Apartment $apartment */
    $apartment = $this->getEntity();
    if (!$apartment instanceof Apartment ||
        !$project = $apartment->getProject()
    ) {
      return [
        '#markup' => '',
      ];
    }
    return [
      '#markup' => $apartment->getApplicationUrl($apartment->id()),
    ];
  }

}
