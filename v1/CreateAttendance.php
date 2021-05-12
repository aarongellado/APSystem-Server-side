<?php

require_once '../includes/DbOperations.php';
date_default_timezone_set("Asia/Manila");
$response = array();
if($_SERVER['REQUEST_METHOD']=='POST'){

  if( 
    isset($_POST['employee_id'],$_POST['siteName'])
    )
    {
      $db = new DbOperations();

      $result = $db->createAttendanceTimeIn($_POST['employee_id'], date("y-m-d"), date("H:i"), $_POST['siteName']);

      if($result==0){//Attendance entry exists, execute input timeout
        $storeTemp = $db->getAttRecord($_POST['employee_id'], date("y-m-d"));
        if(($storeTemp['time_in']!="00:00:00")&&($storeTemp['time_out']=="00:00:00")){
          $result = $db->createAttendanceTimeOut($_POST['employee_id'], date("y-m-d"), date("H:i"));
          $response['error']=false;
          $response['message']="Time out recorded";
        }
        elseif(($storeTemp['time_in']!="00:00:00")&&($storeTemp['time_out']!="00:00:00")){
          $response['error']=true;
          $response['message']="Attendance entry already exists";
        }
      }
      elseif($result==1){
        $response['error']=true;
        $response['message']="Time in recorded";
      }
      elseif($result==2){
        $response['error']=true;
        $response['message']="Some error occured, please try again";
      }
      elseif($result==3){
        $response['error']=true;
        $response['message']="Employee id does not exist";
      }
      


    }
    else{
      $response['error']=true;
      $response['message']="Scan employee QR code";
    }


}
else{
$response['error'] = true;
$response['message']="Invalid Request";
}

echo json_encode($response);