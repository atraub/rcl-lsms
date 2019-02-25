<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
/**
 * Revive Session
 *
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
// Config Files
include_once '../shared/Config.php';
// Authentication File
include_once '../shared/Auth.php';
// Collect GET parameters
$sessionID = $_GET['sessionID'];
$fromSession = $_GET['fromSession'];
$toSession = $_GET['toSession'];
// Header HTML
include_once 'header.php';
$mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
if ($mysql_con->connect_error) {
  trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
}
// MSQL Connection to fetch start and end barcodes
$result = $mysql_con->query("SELECT * FROM session WHERE SessionID = $sessionID");
$row = $result->fetch_array(MYSQLI_ASSOC)
$startBarcode = $row['Start_Barcode'];
$endBarcode = $row['End_Barcode'];
// ORACLE connection to voyager db
$ora_conn = oci_connect($ORA_USERNAME, $ORA_PASSWORD, $ORA_CONNECTION);
if (!$ora_con) {
  $e = oci_error();
  trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
// Get the start call no and the start location
$sql = "SELECT
          location.location_code,
          mfhd_master.normalized_call_no
        FROM
          $LIBRARY_CODE.item_status,
          $LIBRARY_CODE.item_barcode,
          $LIBRARY_CODE.mfhd_item,
          $LIBRARY_CODE.mfhd_master,
          $LIBRARY_CODE.location,
          $LIBRARY_CODE.item_status_type
        WHERE
          (mfhd_item.mfhd_id = mfhd_master.mfhd_id) and
          (item_barcode.item_id = mfhd_item.item_id) and
          (item_status.item_id = item_barcode.item_id) and
          (mfhd_master.location_id = location.location_id) and
          (item_status.item_status = item_status_type.item_status_type) and
          (item_barcode.item_barcode='$startBarcode')";
$stid = oci_parse($ora_con, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid , OCI_RETURN_NULLS);
$startCallNo = $row['NORMALIZED_CALL_NO'];
$startLocation = $row['LOCATION_CODE'];
// Get the end call no and the end location
$sql = "SELECT
          location.location_code,
          mfhd_master.normalized_call_no
        FROM
          $LIBRARY_CODE.item_status,
          $LIBRARY_CODE.item_barcode,
          $LIBRARY_CODE.mfhd_item,
          $LIBRARY_CODE.mfhd_master,
          $LIBRARY_CODE.location,
          $LIBRARY_CODE.item_status_type
        WHERE
          (mfhd_item.mfhd_id = mfhd_master.mfhd_id) and
          (item_barcode.item_id = mfhd_item.item_id) and
          (item_status.item_id = item_barcode.item_id) and
          (mfhd_master.location_id = location.location_id) and
          (item_status.item_status = item_status_type.item_status_type) and
          (item_barcode.item_barcode='$endBarcode')";
$stid = oci_parse($ora_con, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid , OCI_RETURN_NULLS);
$endCallNo = $row['NORMALIZED_CALL_NO'];
$endLocation = $row['LOCATION_CODE'];
// UNIQUE ITEM_IT NOT IMPLEMENTED**************
$sql = "SELECT
          count(item_barcode.item_id)
        FROM
          $LIBRARY_CODE.item_status,
          $LIBRARY_CODE.item_barcode,
          $LIBRARY_CODE.mfhd_item,
          $LIBRARY_CODE.mfhd_master,
          $LIBRARY_CODE.location,
          $LIBRARY_CODE.item_status_type
        WHERE
          (mfhd_item.mfhd_id = mfhd_master.mfhd_id) and
          (item_barcode.item_id = mfhd_item.item_id) and
          (item_status.item_id = item_barcode.item_id) and
          (mfhd_master.location_id = location.location_id) and
          (item_status.item_status = item_status_type.item_status_type) and
          (location.location_code='$startlocation') and
          (mfhd_master.normalized_call_no >= '$startCallNo') and
          (mfhd_master.normalized_call_no <= '$endCallNo')";
$stid = oci_parse($ora_con, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid , OCI_RETURN_NULLS);
if($row != "") {
  $total = $row['COUNT(ITEM_ID)'];
}
else {
  $total = 0;
}
// UNIQUE ITEM_IT NOT IMPLEMENTED**************
$sql = "SELECT
          count(item_barcode.item_id)
        FROM
          $LIBRARY_CODE.item_status,
          $LIBRARY_CODE.item_barcode,
          $LIBRARY_CODE.mfhd_item,
          $LIBRARY_CODE.mfhd_master,
          $LIBRARY_CODE.location,
          $LIBRARY_CODE.item_status_type
        WHERE
          (mfhd_item.mfhd_id = mfhd_master.mfhd_id) and
          (item_barcode.item_id = mfhd_item.item_id) and
          (item_status.item_id = item_barcode.item_id) and
          (mfhd_master.location_id = location.location_id) and
          (item_status.item_status = item_status_type.item_status_type) and
          (location.location_code='$startlocation') and
          (item_status.item_status<>1) and
          (item_status.item_status<>11) and
          (mfhd_master.normalized_call_no >= '$startCallNo') and
          (mfhd_master.normalized_call_no <= '$endCallNo')";
$stid = oci_parse($ora_con, $sql);
oci_execute($stid);
$row = oci_fetch_array($stid , OCI_RETURN_NULLS);
if($row != "") {
  $active = $row['COUNT(ITEM_ID)'];
}
else {
  $active = 0;
}
oci_close($ora_con);
// Update the tblsessions with all the informatiion collected
if($startLocation == $endLocation) {
  $locID = $endLocation;
}
else {
  $locID = $startLocation . ';' . $endLocation;
}
$result = $mysql_con->query("UPDATE
                          session
                        SET
                          Start_N_Callnum='$startCallNo',
                          End_N_Callnum='$endCallNo',
                          TimeNotOnShelf='',
                          TotShelfList='$total',
                          TotActive='$active'
                        WHERE
                          SessionID='$sessionID'");
$mysql_con->close();
?>

Done! <a href='index.php?fromSession=<?php echo $fromSession; ?>&toSession=<?php echo $toSession; ?>'>Back</a>
