<?php
/**
 * @file
 * A PSR-4 compliant plugin loader implementation.
 */

namespace Drupal\objectify_di;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PluginLoader
 * @package Drupal\objectify_di
 */
class Psr4PluginLoader implements PluginLoaderInterface {

  /**
   * Interface to load plugins for.
   *
   * @var string
   */
  protected $interface;

  /**
   * Namespace to load plugins from.
   *
   * @var string
   */
  protected $namespace;

  /**
   * Internal plugin storage.
   *
   * @var array
   */
  protected $plugins = [];

  /**
   * Dependency injection container.
   *
   * @var ContainerBuilder
   */
  protected $container;
  /** @var PluginContainerDependencyLocatorInterface */
  protected $locator;
  /** @var \ReflectionClass[] */
  protected $registeredClasses;
  /** @var bool */
  protected $classRegistrationCompleted = FALSE;

  /**
   * @param ContainerBuilder $container
   * @param PluginContainerDependencyLocatorInterface $locator
   * @throws \Exception
   */
  public function __construct(ContainerBuilder $container, PluginContainerDependencyLocatorInterface $locator) {
    $this->container = $container;
    $this->locator = $locator;

  }

  /**
   * @param string $interface
   */
  public function setInterface($interface) {
    $this->interface = $interface;
  }

  /**
   * @param string $namespace
   */
  public function setNamespace($namespace) {
    $this->namespace = $namespace;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugins() {
    foreach ($this->getRegisteredClasses() as $class => $reflectionClass) {
      $this->getPlugin($class);
    }

    return $this->plugins;
  }

  /**
   * @return \ReflectionClass[]
   */
  private function getRegisteredClasses() {
    if (!$this->classRegistrationCompleted) {
      $this->registerClassesForModulesInPsr4Namespace();
    }
    return $this->registeredClasses;
  }

  /**
   * @throws \Exception
   */
  protected function registerClassesForModulesInPsr4Namespace() {
    if ($this->namespace === NULL) {
      throw new \Exception('Cannot load plugins for NULL namespace.');
    }

    $cid = [
      get_class($this),
      'registerClassesForModulesInPsr4Namespace',
      $this->namespace,
      $this->interface,
    ];
    $cid = implode('.', $cid);

    $classes = [];

    if ($cache = cache_get($cid)) {
      $classes = $cache->data;
    }
    else {
      foreach (objectify_di_get_active_extensions_by_weight() as $extension => $type) {
        $path = drupal_get_path($type, $extension) . $this->getPsr4NamespacePath();
        if (!$files = file_scan_directory($path, '/\.php/')) {
          continue;
        }

        // Autoload all plugins.
        foreach ($files as $file) {
          list($file_path, $filename, $class) = array_values((array) $file);
          $class = 'Drupal\\' . $extension . '\\' . $this->namespace . '\\' . $class;

          require_once DRUPAL_ROOT . '/' . $file_path;
          if (!class_exists($class)) {
            continue;
          }

          $classes[$class] = [
            'extension' => $extension,
            'type' => $type,
          ];
        }
      }

      cache_set($cid, $classes);
    }

    foreach ($classes as $class => $data) {
      if (!class_exists($class)) {
        user_error("Registered class {$class} for {$cid} no longer exists.", E_USER_WARNING);
        continue;
      }

      $reflection = new \ReflectionClass($class);
      if (!$reflection->isInstantiable()) {
        continue;
      }

      if (!$this->checkClassImplementsRequiredInterfaces($reflection)) {
        continue;
      }

      $this->registerClass($reflection, $data['extension'], $data['type']);
    }

    $this->classRegistrationCompleted = TRUE;
  }

  /**
   * Check that a class implements the required classes for this plugin loader.
   *
   * @param \ReflectionClass $reflection
   *
   * @return bool
   */
  protected function checkClassImplementsRequiredInterfaces(\ReflectionClass $reflection) {
    if (is_string($this->interface)) {
      return $reflection->implementsInterface($this->interface);
    }
    elseif (is_array($this->interface)) {
      foreach ($this->interface as $interface) {
        if (!$reflection->implementsInterface($interface)) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get the path to the PSR-4 compliant classes.
   *
   * @return mixed
   */
  protected function getPsr4NamespacePath() {
    return '/src/' . str_replace('\\', '/', $this->namespace);
  }

  /**
   * @param \ReflectionClass $class
   * @param $extension
   * @param $type
   */
  protected function registerClass(\ReflectionClass $class, $extension, $type) {
    $class_name = $class->getName();
    $this->registeredClasses[$class_name] = $class;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin($class) {
    if ($this->classIsRegistered($class) && !$this->hasPlugin($class)) {
      $this->plugins[$class] = $this->instantiatePlugin($class);
    }
    return $this->plugins[$class];
  }

  /**
   * @param string $class
   * @return bool
   */
  private function classIsRegistered($class) {
    $registeredClasses = $this->getRegisteredClasses();
    return isset($registeredClasses[$class]);
  }

  /**
   * @param string $class
   * @return bool
   */
  private function hasPlugin($class) {
    return isset($this->plugins[$class]);
  }

  /**
   * @param $class
   * @return object
   * @throws \Drupal\objectify_di\UnknownPluginException
   */
  private function instantiatePlugin($class) {
    if ($this->classIsRegistered($class)) {
      return $this->locator->initialiseInstanceOfClassByLocatingDependencies($this->getClassFromRegistry($class), $this->container);
    }
    else {
      throw new UnknownPluginException("Plugin '{$class}' is unknown to the system.");
    }
  }

  /**
   * @param string $class
   * @return \ReflectionClass
   */
  private function getClassFromRegistry($class) {
    $registeredClasses = $this->getRegisteredClasses();
    return $registeredClasses[$class];
  }

}
