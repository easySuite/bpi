<?php

namespace Drupal\bpi\Services;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class BpiServiceFactory.
 *
 * @package Drupal\bpi\Services
 */
class BpiServiceFactory {

  /**
   * Takes care of creation of BPI object instance.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Drupal config.
   *
   * @return \Drupal\bpi\Services\BpiService
   */
  static function create(ConfigFactory $config): BpiService {
    $bpiConfig = $config->get('bpi.service_settings');

    return new BpiService(
      $bpiConfig->get('bpi_service_url'),
      $bpiConfig->get('bpi_agency_id'),
      $bpiConfig->get('bpi_api_key'),
      $bpiConfig->get('bpi_secret_key')
    );
  }
}
