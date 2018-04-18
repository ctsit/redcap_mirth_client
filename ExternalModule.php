<?php
/**
 * @file
 * Provides ExternalModule class for REDCap Mirth Connect Client.
 */

namespace REDCapMirthClient\ExternalModule;

require_once 'REDCapMirthClient.php';

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
  function getClient($base_url, $credentials) {
    return new REDCapMirthClient($base_url, $credentials);
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

}
