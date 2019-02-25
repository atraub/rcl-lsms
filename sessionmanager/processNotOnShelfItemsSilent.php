<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
/**
 * New Sessions
 *
 *
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
// Config File
include_once '../shared/Config.php';
$mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
if ($mysql_con->connect_error) {
  trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
}
$sessionID = $_GET["sessionID"];
if ($_GET["List"] == "Y") {
  $result = $mysql_con->query("SELECT * FROM log_not_on_shelf_item WHERE sessionid=" . $sessionID);
  echo("<table border=1>");
  for ($cnt = 0; $cnt < mysqli_num_fields($result); $cnt++) {
    echo("<th>" . mysqli_fetch_field_direct($result,$cnt)->name . "</th>");
  }
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    echo ("<tr>");
    for ($cnt = 0; $cnt < mysqli_num_fields($result); $cnt++) {
      echo ("<td>"  . $row[$cnt] . "&nbsp;</td>");
    }
    echo("</tr>");
  }
  echo("</table>");
  $mysql_con->close();
}
else {
  $mysql_query->query("DELETE FROM log_not_on_shelf_item WHERE Sessionid =" . $sessionID);
  $result = $mysql_con->query("SELECT * FROM session WHERE SessionID=" . $sessionID);
  $row = $result->fetch_array(MYSQLI_ASSOC);
  $firstCallNum = $row["Start_N_Callnum"];
  $lastCallNum = $row["End_N_Callnum"];
  $mysql_query->query("UPDATE session SET TimeNotOnShelf = now() WHERE sessionid=" . $sessionID);
  mysqli_free_result($result);
  //Get locations for a session and create a part of the query string, $locationQuery
  $result2 = $mysql_con->query("SELECT * from session_location WHERE SessionID=" . $sessionID);
  $location = array();
  while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
    $location[count($location)] = $row2['LocationCode'];
  }
  for($i = 0; $i < count($location); $i++){
    if ($i == 0) {
      $locationQuery = "((LOCATION.LOCATION_CODE='" . $location[$i]  . "')";
    }
    else {
      $locationQuery = $locationQuery .  " or (LOCATION.LOCATION_CODE='" . $location[$i]  . "')";
    }
  }
  if ($locationQuery == "" ) {
    $locationQuery = "(LOCATION.LOCATION_CODE='')";
  }
  else {
    $locationQuery = $locationQuery . ")" ;
  }
  $ora_conn = oci_connect($ORA_USERNAME, $ORA_PASSWORD, $ORA_CONNECTION);
  if (!$ora_conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }
  $sql = "SELECT
          ITEM_BARCODE.ITEM_ID
        FROM
          $LIBRARY_CODE.ITEM_STATUS,
          $LIBRARY_CODE.ITEM_BARCODE,
          $LIBRARY_CODE.MFHD_ITEM,
          $LIBRARY_CODE.MFHD_MASTER,
          $LIBRARY_CODE.LOCATION,
          $LIBRARY_CODE.ITEM_STATUS_TYPE
        where
          (MFHD_ITEM.MFHD_ID = MFHD_MASTER.MFHD_ID) and
          (ITEM_BARCODE.ITEM_ID = MFHD_ITEM.ITEM_ID) and
          (ITEM_STATUS.ITEM_ID = ITEM_BARCODE.ITEM_ID) and
          (MFHD_MASTER.LOCATION_ID = LOCATION.LOCATION_ID) and
          (ITEM_STATUS.ITEM_STATUS = ITEM_STATUS_TYPE.ITEM_STATUS_TYPE) and
          $locationQuery and
          (ITEM_STATUS.ITEM_STATUS<>1) and
          (ITEM_STATUS.ITEM_STATUS<>11) and
          (MFHD_MASTER.NORMALIZED_CALL_NO >= '$firstCallNum') and
          (MFHD_MASTER.NORMALIZED_CALL_NO <= '$lastCallNum')";
  $stid = oci_parse($ora_conn, $sql);
  oci_execute($stid);
  $books_to_eliminate = array();
  while ($row = oci_fetch_array($stid , OCI_RETURN_NULLS)) {
    array_push($books_to_eliminate, $row['ITEM_ID']);
  }
  $sql = "SELECT
            ITEM_BARCODE.ITEM_BARCODE,
            ITEM_BARCODE.ITEM_ID,
            ITEM_STATUS.ITEM_STATUS,
            ITEM_STATUS_TYPE.ITEM_STATUS_DESC,
            MFHD_MASTER.LOCATION_ID,
            LOCATION.LOCATION_CODE,
            MFHD_MASTER.NORMALIZED_CALL_NO,
            MFHD_MASTER.DISPLAY_CALL_NO,
            MFHD_ITEM.ITEM_ENUM,
            ITEM.COPY_NUMBER
          FROM
            $LIBRARY_CODE.ITEM,
            $LIBRARY_CODE.ITEM_STATUS,
            $LIBRARY_CODE.ITEM_BARCODE,
            $LIBRARY_CODE.MFHD_ITEM,
            $LIBRARY_CODE.MFHD_MASTER,
            $LIBRARY_CODE.LOCATION,
            $LIBRARY_CODE.ITEM_STATUS_TYPE
          WHERE
            (ITEM_BARCODE.ITEM_ID = ITEM.ITEM_ID) AND
            (MFHD_ITEM.MFHD_ID = MFHD_MASTER.MFHD_ID) and
            (ITEM_BARCODE.ITEM_ID = MFHD_ITEM.ITEM_ID) and
            (ITEM_STATUS.ITEM_ID = ITEM_BARCODE.ITEM_ID) and
            (MFHD_MASTER.LOCATION_ID = LOCATION.LOCATION_ID) and
            (ITEM_STATUS.ITEM_STATUS = ITEM_STATUS_TYPE.ITEM_STATUS_TYPE) and
            $locationQuery and
            ((ITEM_STATUS.ITEM_STATUS = '1') or (ITEM_STATUS.ITEM_STATUS = '11')) and
            (MFHD_MASTER.NORMALIZED_CALL_NO >= '$firstCallNum') and
            (MFHD_MASTER.NORMALIZED_CALL_NO <= '$lastCallNum')";
  $stid = oci_parse($ora_conn, $sql);
  oci_execute($stid);
  $cnt1 = 0;
  $cnt2 = 0;
  while ($row = oci_fetch_array($stid , OCI_RETURN_NULLS)) {
    $cnt1++;
    $result = $mysql_con->query("select Item_ID from log_scanned_item where Item_ID = '$row[1]'");
    if (mysqli_num_rows($result) == 0) {  //if no record found or if not scanned in:
      if(in_array($row['ITEM_ID'], $books_to_eliminate)) {
        continue;
      }
      $cnt2++;
      if ($row["ITEM_ENUM"]) { //if Item_Enum is not null
        $vEnum = str_replace("'","",$row["ITEM_ENUM"]) ;    //get rid of apostrophe in Item_enum  (sometimes ' caused problem in enum)
      }
      else {
        $vEnum = "";
      }
      if ($row["COPY_NUMBER"]) {
        $vCopynum = str_replace("'","",$row["COPY_NUMBER"]);      //get rid of apostrophe
      }
      else {
        $vCopynum = "";
      }
      $mysql_query->query("INSERT INTO
                     log_not_on_shelf_item (BarCode, sessionID, Callnum, ItemID, Enum, Copy_num, LocID)
                   VALUES ('" . $row["ITEM_BARCODE"] . "', " . $sessionID . " , '" . $row["DISPLAY_CALL_NO"] . "', '" .  $row["ITEM_ID"] . "', '" . $vEnum . "', '" . $vCopynum . "', '" .$row["LOCATION_CODE"] . "')") ;
    }
  }
include("header.php");
echo("Complete! \n\nSaved " . $cnt2 . " items out of " . $cnt1 . ".<br /><br />");
//echo "<a href=index.php?fromSession=$fromSession&toSession=$toSession>Back</a>";
echo "<a href=index.php>Back</a>";
//UNIQUE ITEM_ID NOT IMPLEMENTED**************
  $sql = "SELECT
        COUNT(ITEM_BARCODE.ITEM_ID)
      FROM
        $LIBRARY_CODE.ITEM_STATUS,
        $LIBRARY_CODE.ITEM_BARCODE,
        $LIBRARY_CODE.MFHD_ITEM,
        $LIBRARY_CODE.MFHD_MASTER,
        $LIBRARY_CODE.LOCATION,
        $LIBRARY_CODE.ITEM_STATUS_TYPE
      WHERE
        (MFHD_ITEM.MFHD_ID = MFHD_MASTER.MFHD_ID) and
        (ITEM_BARCODE.ITEM_ID = MFHD_ITEM.ITEM_ID) and
        (ITEM_STATUS.ITEM_ID = ITEM_BARCODE.ITEM_ID) and
        (MFHD_MASTER.LOCATION_ID = LOCATION.LOCATION_ID) and
        (ITEM_STATUS.ITEM_STATUS = ITEM_STATUS_TYPE.ITEM_STATUS_TYPE) and
        $locationQuery and
        (MFHD_MASTER.NORMALIZED_CALL_NO >= '$firstCallNum') and
        (MFHD_MASTER.NORMALIZED_CALL_NO <= '$lastCallNum')";
  $stid = oci_parse($ora_conn, $sql);
  oci_execute($stid);
  $row = oci_fetch_array($stid , OCI_RETURN_NULLS);
  if ($row == "") {
      $vTot = 0;
  } else {
      $vTot = $row[0];
  }
//UNIQUE ITEM_ID NOT IMPLEMENTED**************
$sql = "SELECT
          COUNT(ITEM_BARCODE.ITEM_ID)
        FROM
          $LIBRARY_CODE.ITEM_STATUS,
          $LIBRARY_CODE.ITEM_BARCODE,
          $LIBRARY_CODE.MFHD_ITEM,
          $LIBRARY_CODE.MFHD_MASTER,
          $LIBRARY_CODE.LOCATION,
          $LIBRARY_CODE.ITEM_STATUS_TYPE
        where
          (MFHD_ITEM.MFHD_ID = MFHD_MASTER.MFHD_ID) and
          (ITEM_BARCODE.ITEM_ID = MFHD_ITEM.ITEM_ID) and
          (ITEM_STATUS.ITEM_ID = ITEM_BARCODE.ITEM_ID) and
          (MFHD_MASTER.LOCATION_ID = LOCATION.LOCATION_ID) and
          (ITEM_STATUS.ITEM_STATUS = ITEM_STATUS_TYPE.ITEM_STATUS_TYPE) and
          $locationQuery and
          (ITEM_STATUS.ITEM_STATUS<>1) and
          (ITEM_STATUS.ITEM_STATUS<>11) and
          (MFHD_MASTER.NORMALIZED_CALL_NO >= '$firstCallNum') and
          (MFHD_MASTER.NORMALIZED_CALL_NO <= '$lastCallNum')";
  $stid = oci_parse($ora_conn, $sql);
  oci_execute($stid);
//    echo "<br />vloc:" . $location . " <br />FN:" . $firstCallNum . " <br />LN:" . $lastCallNum . "<br />";
//    echo "<br />" .oci_num_rows($stid) . " rows affected<br />";
  $row = oci_fetch_array($stid , OCI_RETURN_NULLS);
  if ($row == "") {
      $vActive = 0;
  } else {
      $vActive = $row[0];
  }
  $mysql_query->query("update session set TotShelfList = " . $vTot . ", TotActive = " . $vActive . ", ItemsNotOnShelf= ". $cnt2 . " where sessionid = " . $sessionID);
  oci_close($ora_conn);
  $mysql_con->close();
} //List=Y or not
?>
