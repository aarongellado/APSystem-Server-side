<?php
require_once '../includes/DbOperations.php';
if(isset($_POST['employee_id'])){
$db = new DbOperations();
  echo json_encode($db->getEmpLeaveList($_POST['employee_id']));
}
else{
  $response['error'] = true;
  $response['message']="Required fields are missing";
}

?>