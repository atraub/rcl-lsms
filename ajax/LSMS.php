<?php 
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
/**
 * LSMS.php
 *
 * This file is called whenever an item is scanned.  It
 * will return a string with fields delimited by '~'
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
// Config File
include_once '../shared/Config.php';
if(isset($_GET['barcode'])) {
  $barcode = $_GET['barcode'];
}
else {
  displayerror();
}
// ORACLE connection to voyager db
$ora_conn = oci_connect($ORA_USERNAME, $ORA_PASSWORD, $ORA_CONNECTION);
if (!$ora_conn) {
  $e = oci_error();
  trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
$sql = set_SQL($barcode);
$stid = oci_parse($ora_conn, $sql);
oci_execute($stid);
$Item_Status = ''; 
$Item_Status_Desc = '';
$cnt = 0;
$result = array();
while ($row = oci_fetch_array($stid , OCI_RETURN_NULLS)) {
  $result = $row;
  if (($row[2] != 1) && ($row[2] != 11)) {    // NOT not charged NOR returned
    $Item_Status = $row[2]; 
    $Item_Status_Desc = $row[3];
  }
  $cnt++;
}
if ($cnt == 0) {
    echo $barcode . "~";
} 
else {
  if  ($Item_Status == '') {
    $Item_Status = $result[2]; 
    $Item_Status_Desc = $result[3];
  }
    echo $result[0] . "~" . $result[1] . "~" . $Item_Status . "~" . $Item_Status_Desc . "~" . $result[4] . "~" . $result[5] . "~" . $result[6] . "~" . $result[7] . "~" . $result[8] . "~" . $result[9];
}
oci_close($ora_conn);
// SQL Statement
function set_SQL($barcode) {
  global $LIBRARY_CODE;
  $sql = "
    SELECT
      item_barcode.item_barcode,
      item_barcode.item_id,
      item_status.item_status,
      item_status_type.item_status_desc,
      mfhd_master.location_id,
      location.location_code,
      mfhd_master.normalized_call_no,
      mfhd_master.display_call_no,
      mfhd_item.item_enum,
      item.copy_number
    FROM
      $LIBRARY_CODE.item,
      $LIBRARY_CODE.item_status,
      $LIBRARY_CODE.item_barcode,
      $LIBRARY_CODE.mfhd_item,
      $LIBRARY_CODE.mfhd_master,
      $LIBRARY_CODE.location,
      $LIBRARY_CODE.item_status_type
    WHERE
      (item_barcode.item_id = item.item_id) and
      (mfhd_item.mfhd_id = mfhd_master.mfhd_id) and
      (item_barcode.item_id = mfhd_item.item_id) and
      (item_status.item_id = item_barcode.item_id) and 
      (mfhd_master.location_id = location.location_id) and
      (item_status.item_status = item_status_type.item_status_type) and
      (item_barcode.item_barcode = '$barcode')
  ";
  
  return $sql;
}
// No Barcode
function displayerror() {
  echo "No barcode found in URL";
  exit();
}
?>
