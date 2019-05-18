<?php

namespace Drupal\objectify_di;

/**
 * Interface PluginLoaderInterface
 * @package Drupal\objectify_di
 */
interface PluginLoaderInterface {

  /**
   * @param string $namespace
   *  The part of the namespace between the module name and the class name.
   * @see objectify_menu.services.yml
   */
  public function setNamespace($namespace);

  /**
   * @param string $interface
   *  The fully qualified name of the interface.
   * @see objectify_menu.services.yml
   */
  public function setInterface($interface);

  /**
   * @param string $class
   * @return object
   * @throws \Drupal\objectify_di\UnknownPluginException
   */
  public function getPlugin($class);

  /**
   * Retrieve the plugins from the loader.
   *
   * @return object[]
   */
  public function getPlugins();

}
