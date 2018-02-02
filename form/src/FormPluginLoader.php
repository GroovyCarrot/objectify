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
use Drupal\objectify_di\PluginContainerDependencyLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class FormPluginLoader
 * @package Drupal\objectify\Plugin
 */
class FormPluginLoader extends Psr4PluginLoader {

  /**
   * {@inheritdoc}
   */
  protected $namespace = 'Form';

  /**
   * {@inheritdoc}
   */
  protected $interface = 'Drupal\\objectify_form\\Form\\FormBuilderInterface';

  /**
   * @var \ReflectionClass[]
   */
  protected $plugins = [];

  /**
   * Dependency injection container.
   *
   * @var ContainerBuilder
   */
  protected $container;

  /**
   * Plugin dependency loader.
   *
   * @var PluginContainerDependencyLocatorInterface
   */
  protected $loader;

  /**
   * FormPluginLoader constructor.
   *
   * @param ContainerBuilder $container
   * @param PluginContainerDependencyLocatorInterface $locator
   */
  public function __construct(ContainerBuilder $container,
                              PluginContainerDependencyLocatorInterface $locator) {
    $this->container = $container;
    $this->loader = $locator;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function registerClass(\ReflectionClass $class, $extension, $type) {
    $class_name = $class->getName();
    $this->plugins[$class_name] = $class;

    $form_id = call_user_func([$class_name, 'formId']);
    if ($form_id) {
      $this->plugins[$form_id] = $class;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return FormBuilderInterface
   */
  public function getPluginsForFormId($form_id) {
    if (!isset($this->plugins[$form_id])) {
      throw new \InvalidArgumentException("Unable to load non-existent plugin for {$form_id}");
    }

    $class = $this->plugins[$form_id];
    return $this->loader->initialiseInstanceOfClassByLocatingDependencies($class, $this->container);
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugins() {
    return $this->plugins;
  }

}
