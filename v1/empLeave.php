<?php

require_once '../includes/DbOperations.php';

$response = array();
if($_SERVER['REQUEST_METHOD']=='POST'){

  if( 
    isset($_POST['employee_id']) and
    isset($_POST['employee_name']) and
    isset($_POST['leave_type']) and
    isset($_POST['leaveDateStart']) and
    isset($_POST['leaveDateEnd']) and
    isset($_POST['leaveRequestStatus'])
    )
    {
      
      $db = new DbOperations();

      $result = $db->requestForLeave($_POST['employee_id'],$_POST['employee_name'],$_POST['leave_type'],$_POST['leaveDateStart'],$_POST['leaveDateEnd'],$_POST['leaveRequestStatus']);

      if($result==1){
        $response['error']=false;
        $response['message']="Request for leave filed";
      }
      elseif($result==2){
        $response['error']=true;
        $response['message']="Some error occured, please try again";
      }
    }
    else{
      $response['error']=true;
      $response['message']="required fields are missing";
    }


}
else{
$response['error'] = true;
$response['message']="Invalid Request";
}

echo json_encode($response);