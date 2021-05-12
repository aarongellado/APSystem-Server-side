<?php

class DbOperations{
  private $con;

  function __construct(){
    require_once dirname(__FILE__).'/DBConnect.php';
    $db = new DbConnect();

    $this->con = $db->connect();
  }

  /* CRUD -> C -> CRUD*/
  function createUser($employee_id, $employee_pass, $firstname, $lastname, $address, $birthdate, $contact_info, $gender, $position_id, $schedule_id, $userAuth){
    if($this->ifUserExist($employee_id)){
      return 0;
    }
    else
      {
        $employee_pass=md5($employee_pass);
      $stmt = $this->con->prepare("INSERT INTO `employees` (`employee_id`, `employee_pass`, `firstname`, `lastname`, `address`, `birthdate`, `contact_info`, `gender`, `position_id`, `schedule_id`, `userAuth`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
      $stmt->bind_param("ssssssssiii",$employee_id, $employee_pass, $firstname, $lastname, $address, $birthdate, $contact_info, $gender, $position_id, $schedule_id, $userAuth);

      if($stmt->execute()){
        return 1;
      }
      else{
        return 2;
      }
    }
  }

  public function userLogin($employee_id, $employee_pass){
    $employee_pass = md5($employee_pass);
    $stmt = $this->con->prepare("SELECT * FROM employees WHERE employee_id = ? AND employee_pass = ?");
    $stmt->bind_param("ss", $employee_id, $employee_pass);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  public function getUserByUsername($employee_id){
    $stmt = $this->con->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s",$employee_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  private function ifUserExist($employee_id){
    $stmt = $this->con->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s",$employee_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;

  }


  ///////////////////////////////////////////////
  //////ATTENDANCE FUNCTIONS START///////////////
  ///////////////////////////////////////////////

  function createAttendanceTimeIn ($employee_id, $date, $time_in, $siteName){

    if($this->ifUserExist($employee_id)){
      if($this->ifAttExist($employee_id,$date)){//checks if attendance exists
        return 0;
      }
      else{//if attendance does not exist, create entry
        $otcCheckVar = $this->onTimeChecker($employee_id, $time_in);
        $stmt = $this->con->prepare("INSERT INTO `attendance` (`employee_id`, `date`, `time_in`,`status`,`siteName`) VALUES (?, ?, ?,?,?);");
        $stmt->bind_param("sssis",$employee_id, $date, $time_in, $otcCheckVar, $siteName);
        if($stmt->execute()){//if attendance entry succeeds
          return 1;
        }
        else{//if attendance entry fails
          return 2;
        }
      }
    }
    else{
      return 3;
    }
  }

  private function ifAttExist($employee_id, $date){//checks if attendance exists
    $stmt = $this->con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->bind_param("ss",$employee_id, $date);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;

  }

  public function getAttRecord($employee_id,$date){//Pulls in corresponding attendance entry info
    $stmt = $this->con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->bind_param("ss",$employee_id, $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function createAttendanceTimeOut($employee_id, $date, $time_out){
    $cATOsubQuery = array();
    $cATOsubQuery = $this->getAttRecord($employee_id, $date);
    $num_hr = abs(strtotime($time_out) - strtotime($cATOsubQuery['time_in']))/(60*60);
    $stmt = $this->con->prepare("UPDATE `attendance` SET time_out = ?, num_hr = ? WHERE employee_id = ? AND `date` = ?;");
    $stmt->bind_param("ssss", $time_out , $num_hr, $employee_id, $date);
    if($stmt->execute()){//if attendance entry succeeds
        return 1;
      }
      else{//if attendance entry fails
        return 2;
      }

  }
  
  public function onTimeChecker($employee_id, $time_in){
    $otcTimeIn = array();
    $stmt = $this->con->prepare("SELECT employees.employee_id, schedules.time_in FROM employees INNER JOIN schedules ON employees.schedule_id = schedules.id WHERE employees.employee_id = ?;");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $otcTimeIn = $stmt->get_result()->fetch_assoc();
    if($time_in<=$otcTimeIn['time_in']){
      return 1;
    }
    else{
      return 0;
    }
    
  }


  ///////////////////////////////////////////////
  //////ATTENDANCE FUNCTIONS END/////////////////
  ///////////////////////////////////////////////


  public function getSiteList(){
    $stmt = $this->con->prepare("SELECT workSiteID, siteName FROM work_sites");
    $stmt->execute();
    $stmt->bind_result($workSiteID, $siteName);
    $siteListNames = array();
    while($stmt->fetch()){
      $temp = array();
      $temp['workSiteID']=$workSiteID;
      $temp['siteName']=$siteName;
      array_push($siteListNames,$temp);
    }
    return $siteListNames;
  }


  public function getAdminAttList(){
    $stmt = $this->con->prepare("SELECT employee_id, `date`, time_in, time_out, siteName FROM attendance");
    $stmt->execute();
    $stmt->bind_result($employee_id, $date, $time_in, $time_out, $siteName);
    $attendance = array();
    while($stmt->fetch()){
      $temp = array();
      $temp['employee_id']=$employee_id;
      $temp['date']=$date;
      $temp['time_in']=$time_in;
      $temp['time_out']=$time_out;
      $temp['siteName']=$siteName;
      array_push($attendance,$temp);
    }
    return $attendance;
  }

  public function getEmpAttList($employee_id){
    $stmt = $this->con->prepare("SELECT employee_id, `date`, time_in, time_out, siteName FROM attendance WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $stmt->bind_result($employee_id, $date, $time_in, $time_out, $siteName);
    $empAttendance = array();
    while($stmt->fetch()){
      $temp = array();
      $temp['employee_id']=$employee_id;
      $temp['date']=$date;
      $temp['time_in']=$time_in;
      $temp['time_out']=$time_out;
      $temp['siteName']=$siteName;
      array_push($empAttendance,$temp);
    }
    return $empAttendance;
  }
  
  public function getEmpLeaveList($employee_id){
    $stmt = $this->con->prepare("SELECT employee_id, `leave_type`, leaveDateStart, leaveDateEnd, leaveRequestStatus FROM employee_leave WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $stmt->bind_result($employee_id, $leave_type, $leaveDateStart, $leaveDateEnd, $leaveRequestStatus);
    $empLeaves = array();
    while($stmt->fetch()){
      $temp = array();
      $temp['employee_id']=$employee_id;
      $temp['leave_type']=$leave_type;
      $temp['leaveDateStart']=$leaveDateStart;
      $temp['leaveDateEnd']=$leaveDateEnd;
      $temp['leaveRequestStatus']=$leaveRequestStatus;
      array_push($empLeaves,$temp);
    }
    return $empLeaves;
  }

  public function requestForLeave ($employee_id, $employee_name, $leave_type, $leaveDateStart, $leaveDateEnd, $leaveRequestStatus){
    
    $stmt = $this->con->prepare("INSERT INTO `employee_leave` (`employee_id`, `employee_name`, `leave_type`, `leaveDateStart`, `leaveDateEnd`, `leaveRequestStatus`) VALUES (?, ?, ?, ?, ?, ?);");
    $stmt->bind_param("ssssss",$employee_id, $employee_name, $leave_type, $leaveDateStart, $leaveDateEnd, $leaveRequestStatus);
    if($stmt->execute()){
      return 1;
    }
    else{
      return 2;
    }
  }




}