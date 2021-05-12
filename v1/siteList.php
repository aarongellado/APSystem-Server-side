<?php
require_once '../includes/DbOperations.php';
$db = new DbOperations();
  echo json_encode($db->getSiteList());
?>