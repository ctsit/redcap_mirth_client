<?php

use ExternalModules\ExternalModules;

require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';

//limit to only logs for a specific project if in a project scope
$limiter = "";
if($_GET['pid']){
  $limiter = " where project_id=" . $_GET['pid'];
}

//get logs
$sql = "select * from redcap_mirth_client_log" . $limiter;
$result = ExternalModules::query($sql);

//convert mysqli obj into associative array
$data = [];
while($row = $result->fetch_assoc()) {
  $data[] = $row;
}

//print table
?>
<div class='table-responsive'>
  <table class='table table-striped'>
    <thead>
        <tr>
          <th>PID</th>
          <th>Method</th>
          <th>URI</th>
          <th>Status Code</th>
          <th>Datetime</th>
          <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data as $log_number => $log) { ?>
      <tr>
      <td><?= $log['project_id'] ?></td>
      <td><?= $log['method'] ?></td>
      <td><?= $log['uri'] ?></td>
      <td><?= $log['status_code'] ?></td>
      <td><?= $log['datetime'] ?></td>
      <td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#log<?= $log_number ?>'>see details</button></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>

<?php
  //create modals that display detailed info about the response and requests
  foreach($data as $log_number => $log) {
?>
<div class="modal fade" id="log<?= $log_number ?>" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Request Details</h4>
      </div>
      <div class="modal-body" style="overflow-wrap:break-word;word-wrap:break-word;">
        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Request</label>
          <div class="col-sm-10"><pre><?= $log['request'] ?></pre></div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Response</label>
          <div class="col-sm-10"><pre><?= $log['response'] ?></pre></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<?php
require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
 ?>
