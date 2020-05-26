<?php

namespace OCA\SocialLogin\Logger;

use Hybridauth\Logger\LoggerInterface;
use OCP\ILogger;

class HybridauthLoggerAdapter implements LoggerInterface
{

  /** @var ILogger */
  private $logger;

  public function __construct(ILogger $logger)
  {
     $this->logger = $logger;
  }

  public function info($message, array $context = array()){
    $this->logger->info($message, $context);
  }

  public function debug($message, array $context = array()){
    $this->logger->debug($message, $context);
  }

  public function error($message, array $context = array()){
    $this->logger->error($message, $context);
  }

  public function log($level, $message, array $context = array()){
    $this->logger->log($message, $context);
  }
}
