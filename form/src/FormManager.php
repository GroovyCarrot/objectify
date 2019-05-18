<?php

namespace Drupal\objectify_form;

use Drupal\objectify_form\Form\FormBuilderInterface;

/**
 * Class FormManager
 * @package Drupal\objectify_form
 */
class FormManager {

  /**
   * Form loader service.
   *
   * @var FormPluginLoader
   */
  protected $formPluginLoader;

  /**
   * FormAlter plugin loader service.
   *
   * @var FormAlterPluginLoader
   */
  protected $formAlterPluginLoader;

  /**
   * Form id locator service.
   *
   * @var FormIdLocator
   */
  protected $formIdLocator;

  /**
   * FormManager constructor.
   *
   * @param FormPluginLoader $form_loader
   * @param FormAlterPluginLoader $form_alter_loader
   * @param FormIdLocator $id_locator
   */
  public function __construct(FormPluginLoader $form_loader,
                              FormAlterPluginLoader $form_alter_loader,
                              FormIdLocator $id_locator) {
    $this->formPluginLoader = $form_loader;
    $this->formAlterPluginLoader = $form_alter_loader;
    $this->formIdLocator = $id_locator;
  }

  /**
   * Implements hook_forms().
   */
  public function getFormCallbacks($form_id, $args) {
    $forms = [];

    /** @var FormBuilderInterface $plugin */
    foreach ($this->formPluginLoader->getPlugins() as $plugin) {
      $form_definition = [
        'callback' => 'objectify_form_load_form',
        'callback arguments' => [get_class($plugin)],
      ];

      $forms[$plugin::formId()] = $form_definition;
    }

    return $forms;
  }

  /**
   * Load a form from a form id.
   *
   * @param string $form_id
   * @param array $form
   * @param array $form_state
   *
   * @return array
   */
  public function getForm($form_id, $form, &$form_state) {
    $form_builder = $this->formPluginLoader->getPluginsForFormId($form_id);
    if (!$form_builder) {
      return [];
    }

    $form['#form_builder'] = $form_builder;

    $args = array_slice(func_get_args(), 3);

    call_user_func_array(
      [$form_builder, 'buildForm'],
      array_merge([&$form, &$form_state], $args)
    );

    return $form;
  }

  /**
   * Implements hook_form_alter().
   */
  public function alterForm(&$form, &$form_state, $form_id) {
    $form_ids = [$form_id];

    $root_id = $this->formIdLocator->locateRootIdForFormId($form_id, $form_state['build_info']['args']);
    if ($root_id) {
      $form_ids[] = $root_id;
    }

    foreach ($form_ids as $form_id) {
      $plugins = $this->formAlterPluginLoader->getPluginsForFormId($form_id);
      foreach ($plugins as $form_builder) {
        $form_builder->buildForm($form, $form_state);
      }
    }
  }

}
