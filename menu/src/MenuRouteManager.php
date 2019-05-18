<?php

namespace Drupal\objectify_menu;

use Drupal\objectify_di\Psr4PluginLoader;

/**
 * Class MenuRouteManager
 * @package Drupal\objectify_menu
 */
class MenuRouteManager {

  /**
   * Plugin loader for menu routes.
   *
   * @var Psr4PluginLoader
   */
  protected $menuRouterPluginLoader;

  /**
   * MenuRouteManager constructor.
   *
   * @param Psr4PluginLoader $router_plugin_loader
   */
  public function __construct(Psr4PluginLoader $router_plugin_loader) {
    $this->menuRouterPluginLoader = $router_plugin_loader;
  }

  /**
   * Build a MenuRouteModel from MenuRoutes provided by plugins.
   *
   * @return MenuRouteModel
   */
  public function getAllMenuRoutes() {
    $items = new MenuRouteModel();

    foreach ($this->menuRouterPluginLoader->getPlugins() as $plugin) {
      $routes = $plugin->getRoutes();
      if (!is_array($routes)) {
        throw new \InvalidArgumentException("Menu routes provided by MenuRouteControllerInterface::getRoutes() must be an array.");
      }

      foreach ($routes as $path => &$route) {
        if ($route instanceof MenuRoute) {
          $items->addRoute($path, $route);
        }
        elseif (is_array($route)) {
          $items->addRoute($path, new MenuRoute($route));
        }
        else {
          throw new \InvalidArgumentException("Routes must be of type MenuRoute or a Drupal hook_menu array.");
        }
      }
    }

    return $items;
  }

  /**
   * Implements hook_menu_alter().
   */
  public function alterMenuRoutes(MenuRouteModel $items) {
    foreach ($this->menuRouterPluginLoader->getPlugins() as $plugin) {
      $plugin->alterRoutes($items);
    }
  }

  /**
   * Implements hook_permission().
   */
  public function getPermissions() {
    $permissions = [];

    foreach ($this->menuRouterPluginLoader->getPlugins() as $plugin) {
      $router_permissions = $plugin->getPermissions();
      if (!is_array($router_permissions)) {
        continue;
      }

      $permissions += $router_permissions;
    }

    return $permissions;
  }

}
