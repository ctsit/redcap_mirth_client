<?php
/**
 * @file
 * Provides ExternalModule class for REDCap Mirth Connect Client.
 */

namespace REDCapMirthClient\ExternalModule;

require_once dirname(__FILE__) . '/REDCapMirthClient.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use REDCapMirthClient\REDCapMirthClient;

/**
 * ExternalModule class for REDCap Mirth Connect Client.
 */
class ExternalModule extends AbstractExternalModule {

  /**
   * Takes in an the base url of the API as an input.
   * returns a REDCapMirthClient object that can be used to send API requests.
   */
  function getClient($endpoint_id) {

    //get configuration for endpoint id
    $config = $this->getEndpointConfig($endpoint_id);

    //return null if not given a valid endpoint_id
    if(is_null($config)) {
      return null;
    }

    //otherwise prepare arguments for generating a new REDCapMirthClient
    $credentials = ["username" => $config['endpoint_username'],
                    "password" => $config['endpoint_password']];
    $endpoint_url = $config['endpoint_url'];

    return new REDCapMirthClient($endpoint_url, $credentials);
  }

  /**
   * Clean up old logs based on an configured expiration time.
   */
  function cleanLogs() {
    $projects = ExternalModules::getEnabledProjects($this->PREFIX);

    //go through each project that has enabled this module
    while ($project = db_fetch_assoc($projects)) {
      $pid = $project['project_id'];

      //if a log lifetime has not been set then do nothing
      if (!$n_days = $this->getProjectSetting('log_lifetime', $pid)) {
        continue;
      }

      $timestamp = strtotime('-' . $n_days . ' days');
      $this->query('DELETE FROM redcap_mirth_client_log WHERE project_id = ' . db_escape($pid) . ' AND UNIX_TIMESTAMP(datetime) < ' . $timestamp);
    }
  }

  /**
   * Get config settings for given endpoint_id.
   * otherwise return null.
   */
  private function getEndpointConfig($endpoint_id) {
    $settings = $this->getSubSettings('endpoint_settings');
    $length = count($settings);

    //search for setting provided
    $setting = null;
    for($i = 0; $i < $length; $i++) {
      if($settings[$i]['endpoint_id'] == $endpoint_id) {
        $setting = $settings[$i];
        break;
      }
    }

    return $setting;
  }
}
