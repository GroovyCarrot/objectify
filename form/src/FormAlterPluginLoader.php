<?php
/**
 * @file
 * FormAlterPluginLoader implementation.
 *
 * Aggregates PSR-4 compliant classes for altering forms, and automatically
 * integrates into Drupal Form API.
 *
 * @see \Drupal\objectify_form\Plugin\FormAlter\FormAlterInterface
 */

namespace Drupal\objectify_form;

use Drupal\objectify_form\Plugin\FormAlter\FormAlterInterface;

/**
 * Class FormAlterPluginLoader
 * @package Drupal\objectify_form\Plugin
 */
class FormAlterPluginLoader extends FormPluginLoader {

  /**
   * {@inheritdoc}
   */
  public function registerClass(\ReflectionClass $class, $extension, $type) {
    $form_ids = call_user_func([$class->getName(), 'formIds']);
    if (!$form_ids || !is_array($form_ids)) {
      return;
    }

    foreach ($form_ids as $form_id) {
      $this->plugins[$form_id][$extension][$class->getName()] = $class;
    }
  }

  /**
   * Retrieve a list of plugins from the loader for a form id.
   *
   * @param string $form_id
   *
   * @return FormAlterInterface[]
   */
  public function getPluginsForFormId($form_id) {
    if (!isset($this->plugins[$form_id])) {
      return [];
    }

    $form_builders = [];
    foreach ($this->plugins[$form_id] as $extension => $plugins) {
      $form_builders += $this->loadPlugins($plugins);
    }

    return $form_builders;
  }

  /**
   * Attach plugins for a module for a form_id to the plugins array.
   *
   * @param \ReflectionClass[] $plugins
   *
   * @return FormAlterInterface[]
   */
  protected function loadPlugins($plugins) {
    $loaded = [];
    foreach ($plugins as $class) {
      $loaded[$class->getName()] = $this->locator->initialiseInstanceOfClassByLocatingDependencies($class, $this->container);
    }
    return $loaded;
  }

}
