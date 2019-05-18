<?php
/**
 * @file
 * FormPluginLoader implementation.
 *
 * This autoloads plugins implementing FormBuilderInterface in the Form
 * namespace of Drupal extensions. Forms are exposed to the form API via their
 * class name, or the id defined by FormBuilderInterface::formId().
 *
 * @see \Drupal\objectify_form\FormBuilderInterface
 */

namespace Drupal\objectify_form;

use Drupal\objectify_form\Form\FormBuilderInterface;
use Drupal\objectify_di\Psr4PluginLoader;

/**
 * Class FormPluginLoader
 * @package Drupal\objectify\Plugin
 */
class FormPluginLoader extends Psr4PluginLoader {

  /** @var \ReflectionClass[] keyed by form id */
  protected $formIdClassMap = [];

  /**
   * {@inheritdoc}
   */
  public function registerClass(\ReflectionClass $class, $extension, $type) {
    parent::registerClass($class, $extension, $type);
    $form_id = call_user_func([$class->getName(), 'formId']);
    if ($form_id) {
      $this->formIdClassMap[$form_id] = $class;
    }
  }

  /**
   * @return string[]
   */
  public function getRegisteredFormIds() {
    return array_keys($this->formIdClassMap);
  }

  /**
   * {@inheritdoc}
   *
   * @return FormBuilderInterface
   */
  public function getPluginsForFormId($form_id) {
    if (!$this->classRegistrationCompleted) {
      $this->registerClassesForModulesInPsr4Namespace();
    }

    if (isset($this->formIdClassMap[$form_id])) {
      $class = $this->formIdClassMap[$form_id];
      return $this->getPlugin($class->getName());
    }
    else {
      return $this->getPlugin($form_id);
    }
  }
}
