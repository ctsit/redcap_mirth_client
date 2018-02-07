<?php

require_once 'vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class MirthLogHandler extends AbstractProcessingHandler {

  public function __construct($level = Logger::INFO, $bubble = true) {
    parent::__construct($level, $bubble);
  }

  protected function write(array $record) {

  }

}

?>
