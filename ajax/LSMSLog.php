<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * LSMSLog.php
 *
 * This file is called to log activity of scanned items
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

// Config File
include_once '../shared/Config.php';

error_reporting(0);

$barcode = $_GET["barcode"];
$callNumber = $_GET["CallNum"];
$enum = $_GET["ENum"];
$locID = $_GET["LocID"];
$location = $_GET["Loc"];
$isNewLoc = $_GET["NewLoc"];
$itemID = $_GET["ItemID"];
$status = $_GET["Status"];
$activeStatusID = $_GET["ActiveStatusID"];
$copyNum = $_GET["Copynum"];
$session = $_GET["Session"];
    if (!$session) $session = 0;  //otherwise you'll get error in myslq_query

//only for session
$firstCall = $_GET["firstCall"];
$currentCall = $_GET["currentCall"];
$counter = $_GET["counter"];
    if (!$counter) $counter=0;
$validCount = $_GET["validcnt"];
    if (!$validCount) $validCount=0;
$firstBar = $_GET["firstBar"];
$currentBar = $_GET["currentBar"];

//if isnull(status) then status = ""

$mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
if ($mysql_con->connect_error) {
  trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
}

if ($status == "w") {
    $mysql_query ("UPDATE
                    log_scanned_item
                  SET
                    status = 'w'
                  WHERE
                    sessionid = ".$session. "
                  ORDER BY
                    CheckDate DESC LIMIT 1");
}



if($status != "w") {
    $mysql_query ("INSERT INTO
                    log_scanned_item (barcode, Item_ID, CallNum, ENum, LocID,Status, ActiveStatusID, Copy_num, SessionID, CheckDate)
                  VALUES
                    ('".$barcode."', '".$itemID."', '".$callNumber."', '".$enum."', '".$location."', '".$status."', '".$activeStatusID."', '".$copyNum."', ".$session.", now())");
} else {
    $mysql_query ("INSERT INTO
                    log_scanned_item (barcode, Item_ID, CallNum, ENum, LocID, ActiveStatusID, Copy_num, SessionID, CheckDate)
                  VALUES
                    ('".$barcode."', '".$itemID."', '".$callNumber."', '".$enum."', '".$location."', '".$activeStatusID."', '".$copyNum."', ".$session.", now())");
}

if (($vCurrentBar != "") || ($vCurrentBar != "undefined")) {

    $mysql_query ("UPDATE
                    session
                  SET
                    End_Time = now(),
                    Start_N_Callnum = '" .$firstCall."',
                    End_N_Callnum= '".$currentCall."',
                    Start_Barcode='".$firstBar."',
                    End_Barcode='".$currentBar."',
                    TotScan=".$counter.",
                    ValScan=".$validCount."
                  WHERE
                    sessionID = ".$session);
}


if ($isNewLoc == "Y") {

    $mysql_query ("INSERT INTO
                    session_location (SessionID, LocationCode)
                  VALUES
                    (" . $session . ", '" . $location . "')");

}




echo (mysqli_error($mysql_con));

$mysql_con->close();


?>
