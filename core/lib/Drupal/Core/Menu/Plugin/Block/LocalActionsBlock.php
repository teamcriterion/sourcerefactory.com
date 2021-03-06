<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Block\LocalActionsBlock.
 */

namespace Drupal\Core\Menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\LocalActionManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a block to display the local actions.
 *
 * @Block(
 *   id = "local_actions_block",
 *   admin_label = @Translation("Primary admin actions")
 * )
 */
class LocalActionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The local action manager.
   *
   * @var \Drupal\Core\Menu\LocalActionManagerInterface
   */
  protected $localActionManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a LocalActionsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\LocalActionManagerInterface $local_action_manager
   *   A local action manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocalActionManagerInterface $local_action_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localActionManager = $local_action_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.menu.local_action'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $route_name = $this->routeMatch->getRouteName();
    $local_actions = $this->localActionManager->getActionsForRoute($route_name);
    if (empty($local_actions)) {
      return [];
    }

    return $local_actions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // The "Primary admin actions" block is never cacheable because hooks creating local
    // actions don't provide cacheability metadata.
    // @todo Remove after https://www.drupal.org/node/2511516 has landed.
    $form['cache']['#disabled'] = TRUE;
    $form['cache']['#description'] = $this->t('This block is never cacheable.');
    $form['cache']['max_age']['#value'] = 0;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // @todo Remove after https://www.drupal.org/node/2511516 has landed.
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['route.name'];
  }

}
