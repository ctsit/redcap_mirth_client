<?php

require_once \ExternalModules\ExternalModules::getProjectHeaderPath();

define('MIRTH_CLIENT_LOGS_MAX_LIST_SIZE', 25);
define('MIRTH_CLIENT_LOGS_MAX_PAGER_SIZE', 10);

$module = new REDCapMirthClient\ExternalModule\ExternalModule();

//Determine current page for pagination
$curr_page = empty($_GET['pager']) ? 1 : $_GET['pager'];

//limit page to only show logs for a specific project if in a project scope
$limiter = "";
if($_GET['pid']){
  $limiter = " where project_id=" . $_GET['pid'];
}

//get number of log entries
$result = $module->query("select COUNT(*) as total_rows from redcap_mirth_client_log" . $limiter);
$total_rows = $result->fetch_assoc();
$total_rows = $total_rows['total_rows'];

//calculate the total number of pages
$num_pages = (int) ($total_rows / MIRTH_CLIENT_LOGS_MAX_LIST_SIZE);
if ($total_rows % MIRTH_CLIENT_LOGS_MAX_LIST_SIZE) {
    $num_pages++;
}

//calculate the pager size.
$pager_size = MIRTH_CLIENT_LOGS_MAX_PAGER_SIZE;
if ($num_pages < $pager_size) {
    $pager_size = $num_pages;
}

//get actual log entries
$sql = "select * from redcap_mirth_client_log"
       . $limiter
       . " ORDER BY datetime DESC"
       . " LIMIT " . MIRTH_CLIENT_LOGS_MAX_LIST_SIZE
       . " OFFSET " . (($curr_page - 1) * MIRTH_CLIENT_LOGS_MAX_LIST_SIZE);
$result = $module->query($sql);

//convert mysqli obj into associative array
$data = [];
while($row = $result->fetch_assoc()) {
  $data[] = $row;
}
?>

<!-- Mirth Log Table -->
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
    <?php foreach($data as $log_number => $log): ?>
      <tr>
      <td><?= $log['project_id'] ?></td>
      <td><?= $log['method'] ?></td>
      <td><?= $log['uri'] ?></td>
      <td><?= $log['status_code'] ?></td>
      <td><?= $log['datetime'] ?></td>
      <td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#log<?= $log_number ?>'>see details</button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Mirth Modals for "see detail" buttons -->
<?php foreach($data as $log_number => $log): ?>
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
<?php endforeach; ?>

<!--Nav bar used for pagination -->
<nav aria-label="Mirth Client Logs Navigation">
    <ul class="pagination">
        <?php for($i = 1; $i <= $pager_size; $i++): ?>
          <li class="page-item <?php echo ($i == $curr_page) ? "active" : "" ?>">
            <a class="page-link" href="<?= $module->getUrl("MirthLog.php") . "&pager=" . $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
    </ul>
</nav>
