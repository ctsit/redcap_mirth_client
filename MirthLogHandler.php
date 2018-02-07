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

  //parses the backslash delimeted log message for the desired data
  private function parse_message($message) {
    preg_match_all('/([^\\\\]+)/', $message, $matches);
    $matches = $matches[0];
    return array(
      'method' => $matches[0],
      'uri' => $matches[1],
      'status_code' => $matches[2],
      'request' => $matches[3],
      'response' => $matches[4]
    );
  }

  private function construct_query(array $data) {
    extract($data);
    return "INSERT INTO redcap_mirth_client_log (project_id, method, uri, status_code, request, response, datetime) VALUES ($project_id, '$method', '$uri', '$status_code', '$request', '$response', '$datetime')";
  }

}

?>
