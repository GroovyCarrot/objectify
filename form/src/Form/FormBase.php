<?php
/**
 * @file
 * FormBase definition.
 */

namespace Drupal\objectify_form\Form;

/**
 * Interface FormBuilderInterface
 * @package Drupal\objectify\Form
 */
abstract class FormBase implements FormBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public static function formId() {
    return '';
  }

  /**
   * Extract a value from the form state.
   *
   * @param string $element_id
   *   Value to fetch from form state. Use . to retrieve nested values. I.e.
   *   colour.rgb.r will return $form_state['values']['colour']['rgb']['r'].
   * @param array $form_state
   *   Form state.
   *
   * @return mixed|NULL
   */
  protected function getValue($element_id, array $form_state) {
    $value = $form_state['values'];

    foreach (explode('.', $element_id) as $element) {
      if (!isset($value[$element])) {
        return NULL;
      }
      $value = $value[$element];
    }

    return $value;
  }

  /**
   * Get an argument passed into the form.
   *
   * @param array $form_state
   *   Form state.
   * @param int $index
   *   Index of the argument to return.
   *
   * @return mixed|NULL
   */
  protected function getFormArgument(array $form_state, $index = 0) {
    if (!isset($form_state['build_info']['args'][$index])) {
      return NULL;
    }

    return $form_state['build_info']['args'][$index];
  }

}
