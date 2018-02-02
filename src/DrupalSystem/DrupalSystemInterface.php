<?php
/**
 * @file
 * A system class intended to wrap Drupal system functionality, in order to
 * correctly decouple classes from Drupal.
 */

namespace Drupal\objectify\DrupalSystem;

use Drupal\xautoload\DrupalSystem\DrupalSystemInterface as XautoloadDrupalSystemInterface;

/**
 * Interface SystemInterface
 * @package Drupal\objectify
 */
interface DrupalSystemInterface extends XautoloadDrupalSystemInterface {

  /**
   * @see drupal_static()
   *
   * @param string $name
   * @param mixed $default_value
   * @param bool $reset
   * @return mixed
   */
  public function &drupalStatic($name, $default_value = NULL, $reset = FALSE);

  /**
   * @see drupal_get_path()
   *
   * @param string $type
   * @param string $name
   * @return string
   */
  public function drupalGetPath($type, $name);

  /**
   * @see render()
   *
   * @param $element
   * @return string
   */
  public function render(&$element);

  /**
   * @see drupal_render()
   *
   * @param $elements
   * @return string
   */
  public function drupalRender(&$elements);

  /**
   * Get the current logged in User.
   *
   * @return \stdClass
   */
  public function getUser();

  /**
   * Get the global $conf array.
   *
   * @return array
   */
  public function &getConfig();

  /**
   * @see drupal_goto()
   *
   * @param string $path
   * @param array $options
   * @param int $http_response_code
   */
  public function drupalGoto($path = '', array $options = [], $http_response_code = 302);

  /**
   * @see drupal_set_message()
   *
   * @param string $message
   * @param string $type
   * @param bool $repeat
   * @return array|null
   */
  public function drupalSetMessage($message = NULL, $type = 'status', $repeat = TRUE);

  /**
   * @see module_invoke_all()
   *
   * @param string $hook
   * @param mixed $parameter
   * @param mixed $_
   * @return array
   */
  public function moduleInvokeAll($hook, $parameter = NULL, $_ = NULL);

  /**
   * @see module_invoke()
   *
   * @param string $module
   * @param string $hook
   * @return mixed
   */
  public function moduleInvoke($module, $hook);

  /**
   * @see drupal_alter().
   *
   * @param string $type
   * @param $data
   * @param $context1
   * @param $context2
   * @param $context3
   */
  public function drupalAlter($type, &$data, &$context1 = NULL, &$context2 = NULL, &$context3 = NULL);

  /**
   * @see format_date()
   *
   * @param $timestamp
   * @param string $type
   * @param string $format
   * @param null $timezone
   * @param null $langcode
   * @return string
   */
  public function formatDate($timestamp, $type = 'medium', $format = '', $timezone = NULL, $langcode = NULL);

  /**
   * @see drupal_get_breadcrumb()
   *
   * @return array
   */
  public function drupalGetBreadcrumb();

  /**
   * @see drupal_get_form()
   *
   * @param $form_id
   * @param array $arguments
   * @return array
   */
  public function drupalGetForm($form_id, array $arguments = []);

  /**
   * Call a function...
   *
   * It's likely going to be unavoidable that there's a procedural function that
   * needs to be called. It's pretty impossible to write a fully abstracted OO
   * api on Drupal 7, and if that is what you are after - you may as well just
   * use Drupal 8.
   *
   * Using this method, at least you have the option to mock this object; rather
   * than being required to add Drupal to phpunit bootstrap, and allowing the
   * function to execute it's logic during your test.
   *
   * Example usage
   *   $this->system->callFunction('drupal_set_title', [$this->t('A title for a page')]);
   * Passing a variable by reference:
   *   $system->callFunction('another_function', [$var1, &var2_by_reference]);
   *
   * @param string $func
   * @param array $args
   * @return mixed
   */
  public function callFunction($func, array $args = []);

  /**
   * @see current_path()
   *
   * @return string
   */
  public function currentPath();

}
