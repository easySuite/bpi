<?php

namespace Drupal\bpi\Services;

use Bpi\Sdk\Bpi;

/**
 * Class BpiService.
 *
 * Provides access to BPI logic.
 *
 * @package Drupal\bpi\Services
 */
class BpiService {

  private $bpiInstance = NULL;

  /**
   * BpiService constructor.
   *
   * @param string $url
   *   Bpi service endpoint.
   * @param string $agency
   *   Agency id.
   * @param string $publicKey
   *   Public key.
   * @param string $privateKey
   *   Private key.
   */
  public function __construct($url, $agency, $publicKey, $privateKey) {
    $this->bpiInstance = new Bpi($url, $agency, $publicKey, $privateKey);
  }

  /**
   * Allows to check connectivity to the BPI service.
   *
   * This method is primarily used in settings form, validation
   * method, when config settings are not yet available.
   *
   * @param string $url
   *   Bpi service endpoint.
   * @param string $agency
   *   Agency id.
   * @param string $publicKey
   *   Public key.
   * @param string $privateKey
   *   Private key.
   */
  public function checkConnectivity($url, $agency, $publicKey, $privateKey) {
    $bpi = new Bpi($url, $agency, $publicKey, $privateKey);

    // Fake a request, to check connectivity.
    // TODO: Might be slow and unreliable, add a method in Bpi to check connectivity.
    $bpi->searchNodes(['*']);
  }

  /**
   * Returns current bpi instance.
   *
   * @return \Bpi\Sdk\Bpi|null
   */
  public function getInstance(): ?Bpi {
    return $this->bpiInstance;
  }
}
