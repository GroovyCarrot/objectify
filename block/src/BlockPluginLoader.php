<?php

namespace Drupal\objectify_block;

use Drupal\objectify_block\Exception\NonExistentBlockPluginException;
use Drupal\objectify_block\Plugin\Block\BlockAutoPluginInterface;
use Drupal\objectify_block\Plugin\Block\BlockInterface;
use Drupal\objectify_di\Psr4PluginLoader;

/**
 * Class BlockPluginLoader
 * @package Drupal\objectify_block
 */
class BlockPluginLoader extends Psr4PluginLoader {

  /**
   * Internal storage for class mappings.
   *
   * @var array
   */
  protected $classes = [];

  /**
   * Classes to automatically plug into Drupal.
   *
   * @var array
   */
  protected $autowire = [];

  /**
   * {@inheritdoc}
   */
  public function registerClass(\ReflectionClass $class, $extension, $type) {
    $delta = $this->blockClassNameToDelta($class->getShortName());
    $this->classes[$extension][$delta] = $class;

    if ($class->implementsInterface('Drupal\\objectify_block\\Plugin\\Block\\BlockAutoPluginInterface')) {
      $internal_delta = $extension . '.' . $delta;
      $this->classes['objectify_block'][$internal_delta] = &$this->classes[$extension][$delta];
      $this->autowire[$internal_delta] = &$this->classes[$extension][$delta];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return BlockInterface
   * @throws NonExistentBlockPluginException
   */
  public function getPlugin($block) {
    if (isset($this->plugins[$block->module][$block->delta])) {
      return $this->plugins[$block->module][$block->delta];
    }

    if (!isset($this->classes[$block->module][$block->delta])) {
      throw new NonExistentBlockPluginException("Unable to load non-existent plugin for block: {$block->module}:{$block->delta}.");
    }

    /** @var \ReflectionClass $class */
    $class = $this->classes[$block->module][$block->delta];
    $args = [$block];
    $plugin = $this->locator->initialiseInstanceOfClassByLocatingDependencies($class, $this->container, $args);
    return $this->plugins[$block->module][$block->delta] = $plugin;
  }

  /**
   * {@inheritdoc}
   *
   * Only called in the context of plugins that are autowired, no need to load
   * all of them. Also, this should only be called by hook_block_info when the
   * cache is empty.
   *
   * @return BlockAutoPluginInterface[]
   */
  public function getPlugins() {
    $autowire_plugins = [];
    foreach ($this->autowire as $delta => $plugin) {
      $pseudo_block = new \stdClass();
      $pseudo_block->module = 'objectify_block';
      $pseudo_block->delta = $delta;
      $autowire_plugins[$delta] = $this->getPlugin($pseudo_block);
    }
    return $autowire_plugins;
  }

  /**
   * Convert a class short name into a block delta string.
   *
   * @param string $class_short_name
   *
   * @return string
   */
  protected function blockClassNameToDelta($class_short_name) {
    return strtolower(preg_replace('/\B([A-Z])/', '_$1', $class_short_name));
  }

}
