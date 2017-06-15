<?php

namespace Drupal\maintenance200\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\MaintenanceModeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class MaintenanceModeSubscriber implements EventSubscriberInterface {

  /**
   * The maintenance mode.
   *
   * @var \Drupal\Core\Site\MaintenanceMode
   */
  protected $maintenanceMode;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MaintenanceModeSubscriber constructor.
   *
   * @param \Drupal\Core\Site\MaintenanceModeInterface $maintenance_mode
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(MaintenanceModeInterface $maintenance_mode, AccountInterface $account, ConfigFactoryInterface $config_factory) {
    $this->maintenanceMode = $maintenance_mode;
    $this->account = $account;
    $this->configFactory = $config_factory;
  }

  /**
   * Return 200 status code if site is in maintenance mode.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    if ($this->configFactory->get('maintenance200.settings')->get('maintenance200_enabled')) {
      $status_code = $this->configFactory->get('maintenance200.settings')->get('maintenance200_status_code');
      $request = $event->getRequest();
      $response = $event->getResponse();
      $route_match = RouteMatch::createFromRequest($request);
      if ($this->maintenanceMode->applies($route_match)) {
        if (is_numeric($status_code) && !$this->maintenanceMode->exempt($this->account)) {
          // Return status code of 200 (instead of 503), if the site is in maintenance mode and the
          // logged in user is not allowed to bypass it. By doing this, varnish will cache the maintenance
          // page and serve it to new requests while in maintenance mode.
          $response->setStatusCode($status_code);
          $event->setResponse($response);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('onKernelResponse', 31);
    return $events;
  }

}
