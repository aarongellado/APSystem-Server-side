<?php

require_once '../includes/DbOperations.php';

$response = array();

if($_SERVER['REQUEST_METHOD']=='POST'){
  if(isset($_POST['employee_id']) and isset($_POST['employee_pass'])){
    $db = new DbOperations();
    
    if($db->userLogin($_POST['employee_id'], $_POST['employee_pass'])){
      $user = $db->getUserByUsername($_POST['employee_id']);
      $response['error'] = false;
      $response['employee_id'] = $user['employee_id'];
      $response['employee_pass'] = $user['employee_pass'];
      $response['firstname'] = $user['firstname'];
      $response['lastname'] = $user['lastname'];
      $response['address'] = $user['address'];
      $response['birthdate'] = $user['birthdate'];
      $response['contact_info'] = $user['contact_info'];
      $response['gender'] = $user['gender'];
      $response['position_id'] = $user['position_id'];
      $response['schedule_id'] = $user['schedule_id'];
      $response['userAuth'] = $user['userAuth'];
      $response['created_on'] = $user['created_on'];
      
    }
    else{
      $response['error'] = true;
      $response['message']="Invalid username or Password";
    }

  }

  else{
    $response['error'] = true;
    $response['message']="Required fields are missing";
  }
}
echo json_encode($response);