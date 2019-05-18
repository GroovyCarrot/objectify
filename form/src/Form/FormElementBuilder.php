<?php

namespace Drupal\objectify_form\Form;

class FormElementBuilder {

  /**
   * @param string|array $class
   * @return array
   */
  public static function containerElementWithClass($class) {
    return self::addClassToElement($class, self::containerElement());
  }

  /**
   * @param string|array $class
   * @param array $element
   * @return array
   */
  private static function addClassToElement($class, array $element) {
    $classes = (array) $class;

    foreach ($classes as $class) {
      $element['#attributes']['class'][] = $class;
    }

    return $element;
  }

  /**
   * @param array $options
   * @param array $element
   * @return array
   */
  private static function addOptionsToElement(array $options, array $element) {
    $element['#options'] = $options;

    return $element;
  }

  /**
   * @return array
   */
  public static function containerElement() {
    return [
      '#type' => 'container'
    ];
  }

  /**
   * @param string $title
   * @param mixed $defaultValue
   * @param bool $required
   * @return mixed
   */
  public static function textFieldWithDefaultValue($title, $defaultValue, $required = FALSE) {
    return self::addDefaultValueToElement($defaultValue, self::textField($title, $required));
  }

  /**
   * @param mixed $defaultValue
   * @param array $element
   * @return array
   */
  public static function addDefaultValueToElement($defaultValue, array $element) {
    $element['#default_value'] = $defaultValue;

    return $element;
  }

  /**
   * @param string $title
   * @param bool $required
   * @return array
   */
  public static function checkboxField($title, $required = FALSE) {
    return self::simpleField('checkbox', $title, $required);
  }

  /**
   * @param string $title
   * @param array $options
   * @param bool $required
   * @return array
   */
  public static function selectField($title, array $options, $required = FALSE) {
    return self::addOptionsToElement(
      $options,
      self::simpleField('select', $title, $required)
    );
  }

  /**
   * @param string $title
   * @param bool $required
   * @return array
   */
  public static function textField($title, $required = FALSE) {
    return self::simpleField('textfield', $title, $required);
  }

  /**
   * @param $type
   * @param $title
   * @param bool $required
   * @return array
   */
  public static function simpleField($type, $title, $required = FALSE) {
    return [
      '#type' => $type,
      '#title' => $title,
      '#required' => $required,
    ];
  }

  /**
   * @param mixed $value
   * @return array
   */
  public static function valueElement($value) {
    return [
      '#type' => 'value',
      '#value' => $value,
    ];
  }

  /**
   * @param string $title
   * @param mixed $defaultValue
   * @param bool $required
   * @return array
   */
  public static function dateFieldWithDefaultValue($title, $defaultValue, $required = FALSE) {
    return self::addDefaultValueToElement($defaultValue, self::dateField($title, $required));
  }

  /**
   * @param string $title
   * @param bool $required
   * @return array
   */
  public static function dateField($title, $required = FALSE) {
    return self::simpleField('date', $title, $required);
  }
}
