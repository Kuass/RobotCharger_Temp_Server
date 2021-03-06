<?php
function socket_display_error($error_code, $error_message)
{
  $list = array("error_code" => $error_code, "error_message" => $error_message);
  echo (json_encode($list));
}

header('Content-Type: application/json; charset=UTF-8');

if (!in_array('application/json', explode(";", $_SERVER['CONTENT_TYPE']))) {
  socket_display_error("07", "JSON_ERROR");
  exit;
}

function isValidJSON($str)
{
  json_decode($str);
  return json_last_error() == JSON_ERROR_NONE;
}

$json_params = file_get_contents("php://input");
if (strlen($json_params) > 0 && isValidJSON($json_params)) {
  $data = json_decode($json_params, true);
} else {
  socket_display_error("07", "JSON_ERROR");
  exit;
}

if (!isset($data["seq"]) && !isset($data["cid"]) && !isset($data["c_stat"])
    && !isset($data["c_cnt"]) && !isset($data["c_park_ty"]) && !isset($data["car_num"])
    && !isset($data["c_voltage"]) && !isset($data["c_ampare"]) && !isset($data["c_kwh"])
    && !isset($data["c_kwh"]) && !isset($data["c_soc"]) && !isset($data["c_stt"])
    && !isset($data["c_ret"])) {
  socket_display_error("09", "There is a parameter that was not passed.");
  exit;
}

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
require_once "Database.php";
$dbconn = mysqli_connect(
  $GLOBALS['db_host'],
  $GLOBALS['db_user'],
  $GLOBALS['db_password'],
  $GLOBALS['db_database']
);

$ip = $_SERVER['REMOTE_ADDR'];
$port = $_SERVER['SERVER_PORT'];
if (isset($_SERVER['HTTP_USER_AGENT'])) {
  $agent = $_SERVER['HTTP_USER_AGENT'];
} else {
  $agent = "NULL";
}
//$seq = $data["seq"];
$cid = $data["cid"];
$c_stat = $data["c_stat"];
$c_cnt = $data["c_cnt"];
$c_park_ty = $data["c_park_ty"];
$car_num = $data["car_num"];
$c_voltage = $data["c_voltage"];
$c_ampare = $data["c_ampare"];
$c_kwh = $data["c_kwh"];
$c_soc = $data["c_soc"];
$c_stt = $data["c_stt"];
$c_ret = $data["c_ret"];
$s_cmd = 0;

$result = $dbconn->query("INSERT INTO h_kiosk (cid, c_stat, c_cnt, c_park_ty, car_num, c_voltage, c_ampare, c_kwh, c_soc, c_stt, c_ret) VALUES 
                                              ('$cid', '$c_stat', '$c_cnt', '$c_park_ty', '$car_num', '$c_voltage', '$c_ampare', '$c_kwh', '$c_soc', '$c_stt', '$c_ret')");
if (!$result) die(socket_display_error("06", "Internal error X2"));
$result = $dbconn->query("SELECT u_cmd FROM h_application ORDER BY `No` DESC LIMIT 1");
if (!$result) die(socket_display_error("06", "Internal error X3"));
$result_object = $result->fetch_object();

$list = array(
  "seq" => 0, "cid" => $cid, "s_cmd" => $result_object->u_cmd
);
die (json_encode($list));
