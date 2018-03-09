<?php

require_once 'vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use ExternalModules\AbstractExternalModule;

class MirthLogHandler extends AbstractProcessingHandler {
  private $project_id;

  public function __construct($project_id, $level = Logger::INFO, $bubble = true) {
    parent::__construct($level, $bubble);
    $this->initialize();
    $this->project_id = $project_id;
  }

  public function amend_last_log($values) {
    $query = $this->construct_amend_query($values);
    AbstractExternalModule::query($query);
  }

  protected function write(array $record) {
    //build and consolidate data to store in db
    $data = $this->parse_message($record['message']);
    $data['project_id'] = $this->project_id;
    $data['datetime'] = $record['datetime']->format('Y-m-d H:i:s');

    //store in db
    $query = $this->construct_write_query($data);
    AbstractExternalModule::query($query);
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

  private function construct_amend_query($values) {
    $query = "UPDATE redcap_mirth_client_log SET ";

    foreach($values as $field => $value) {
      $query .= "$field = '$value',";
    }

    //remove last comma
    $query = substr_replace($query, "", -1);
    $query .=  " WHERE project_id='" . $this->project_id . "'";
    $query .= " ORDER BY datetime DESC LIMIT 1";
    return $query;
  }

  private function construct_write_query($data) {
    extract($data);
    return "INSERT INTO redcap_mirth_client_log (project_id, method, uri, status_code, request, response, datetime) VALUES ('$project_id', '$method', '$uri', '$status_code', '$request', '$response', '$datetime')";
  }

  private function initialize() {
    AbstractExternalModule::query("CREATE TABLE IF NOT EXISTS redcap_mirth_client_log (
        project_id INTEGER,
        method VARCHAR(7),
        uri TEXT,
        status_code VARCHAR(3),
        request TEXT,
        response TEXT,
        datetime TIMESTAMP NOT NULL default CURRENT_TIMESTAMP
    )");
  }


}

?>
