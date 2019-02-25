<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * Process Sessions
 *
 * This file is responsible for handling options of the
 * session manager.
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

// Config File
include_once '../shared/Config.php';
// Authentication File
include_once '../shared/Auth.php';
error_reporting (E_ALL);

// Collect GET parameters
$submit = $_GET["submit"];
$getArray  = explode('&', $_SERVER['QUERY_STRING']);

$j = 0;
for($i = 0; $i < count($getArray); $i++) {
  $getPair = explode('=', $getArray[$i]);
  if($getPair[0] == "SessionID") {
    $sessionsArray[$j] = $getPair[1];
    $j++;
  }
}

$fromSession = $_GET["fromSession"];
$toSession = $_GET["toSession"];

// Used for excel exporting
$arraytoxls = array( 1=> array());
$rowcounter = 2;

// Header HTML


if(!isSet($_GET['SessionID']) || $_GET['SessionID'] == "") {
  include_once 'header.php';
  echo "No Session ID has been selected<br /><a href='index.php?fromSession=$fromSession&toSession=$toSession'>Back</a>";
  return 0;
}

$mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
if ($mysql_con->connect_error) {
  trigger_error('Database connection failed: ' . $mysql_con->connect_error, E_USER_ERROR);
}

// Process cases based on $submit variable
if($submit == "List Not-On-Shelf items") {
  include_once 'header.php';

  echo "
  <h1>
    <img  src='../img/sm-nosi.png' alt='LSMS - List Not on Shelf Items'/>
    <button style='padding: 5px;font-size:13px; margin-left: 30px; padding-bottom: -20px;' onclick=\"window.location = 'index.php?fromSession=$fromSession&toSession=$toSession'\">Back</button>
  </h1>";


  $where = "";

  for($i = 0; $i < sizeof($sessionsArray); $i++) {
    if($i == 0) {
      $where = " SessionID = " . $sessionsArray[$i];
    }
    else {
      $where = $where . " or SessionID = " . $sessionsArray[$i];
    }
  }

  $sql = "SELECT * FROM
          (SELECT log_not_on_shelf_item.* FROM log_not_on_shelf_item LEFT JOIN log_scanned_item ON (log_not_on_shelf_item.ItemID = log_scanned_item.ITEM_ID)
          WHERE log_scanned_item.ITEM_ID Is Null) AS tmpTable WHERE " . $where;

  $result = $mysql_con->query($sql);

  $col_count = mysqli_num_fields($result);

  echo "<a href='phpToExcel.php?filename=Not on shelf'>Export to Excel</a>";
  echo "<table id='session-manager-tbl'>
  <thead>
  <tr>";

  for($col_num = 0; $col_num < $col_count; $col_num++) {
    $name_info = $result->fetch_field_direct($col_num);
    $field_name = $name_info->name;
    echo("  <th>$field_name</th>");
    $arraytoxls[1][] = $field_name;
  }
  echo("  </thead></tr>");

  while($row = $result->fetch_array(MYSQLI_NUM)) {
    echo "<tr id='processed-items'>\n";
    for($col_num = 0; $col_num < $col_count; $col_num++) {
      echo "  <td>" . $row[$col_num] . "</td>\n";
      $arraytoxls[$rowcounter][] = $row[$col_num];
    }
    echo "</tr>\n";
    $rowcounter++;
  }
  echo "</table>\n";
  $_SESSION['arraytoxls'] = $arraytoxls;
  $mysql_con->close();

}
else if($submit == "List Not-On-Shelf items with Title") {
  include_once 'header.php';
  echo "
  <h1>
    <img src='../img/sm-nosiwt.png' alt='LSMS - List Not on Shelf Items With Title'/>
  </h1>";

  $where = "";

  for($i = 0; $i < sizeof($sessionsArray); $i++) {
    if($i == 0) {
      $where = " SessionID = " . $sessionsArray[$i];
    }
    else {
      $where = $where . " or SessionID = " . $sessionsArray[$i];
    }
  }

  $result = $mysql_con->query("SELECT * FROM parameter");
  $row = $result->fetch_array(MYSQLI_ASSOC);

  $sql = "SELECT * FROM
          (SELECT log_not_on_shelf_item.* FROM log_not_on_shelf_item LEFT JOIN log_scanned_item ON (log_not_on_shelf_item.ItemID = log_scanned_item.ITEM_ID) WHERE log_scanned_item.ITEM_ID Is Null) as tmpTable WHERE " . $where;

  $result = $mysql_con->query ($sql);
  $col_count = mysqli_num_fields($result);

  echo "<a href='phpToExcel.php?filename=Not on shelf with title'>Export to Excel</a>";
  echo "<table id='session-manager-tbl'>
  <thead>
  <tr>\n";

  for($col_num = 0; $col_num < $col_count; $col_num++) {
    $name_info = $result->fetch_field_direct($col_num);
    $field_name = $name_info->name;
    echo("  <th>$field_name</th>\n");
    $arraytoxls[1][] = $field_name;
    if($col_num == 3) {
      echo("<th>Title</th>\n");
      $arraytoxls[1][] = "Title";
    }
  }
  echo("  </thead></tr>\n");

  // ORACLE connection to voyager db
  $ora_conn = oci_connect($ORA_USERNAME, $ORA_PASSWORD, $ORA_CONNECTION);
  if (!$ora_conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }



  while($row = $result->fetch_array(MYSQLI_NUM)) {
    echo "<tr id='processed-items'>\n";
    for($col_num = 0; $col_num < $col_count; $col_num++) {
      echo "  <td>" . $row[$col_num] . "</td>\n";
      $arraytoxls[$rowcounter][] = $row[$col_num];
      if($col_num == 3) {
        $itemID = $row[3];

          // Get the start call no and the start location
        $sql = "SELECT bib_text.title_brief
          FROM
            $LIBRARY_CODE.mfhd_item,
            $LIBRARY_CODE.bib_mfhd,
            $LIBRARY_CODE.bib_text,
            $LIBRARY_CODE.item
          WHERE
            (bib_mfhd.bib_id = bib_text.bib_id) and
            (mfhd_item.mfhd_id = bib_mfhd.mfhd_id) and
            (item.item_id = mfhd_item.item_id) and
            item.item_id = '$itemID'";
        $stid = oci_parse($ora_conn, $sql);
        oci_execute($stid);
        $voyagerRow = oci_fetch_array($stid , OCI_RETURN_NULLS);
        $title = $voyagerRow['TITLE_BRIEF'];

        if($title != "") {
          echo("  <td>$title &nbsp</td>\n");
          $arraytoxls[$rowcounter][] = $title;
        }
        else {
          echo("  <td><font color = red>No record match to this ITEM_ID.</font></td>");
          $arraytoxls[$rowcounter][] = "No record match to this ITEM_ID";
        }
      }
    }
    echo "</tr>\n";
    $rowcounter++;
  }
  echo "</table>\n";
  $_SESSION['arraytoxls'] = $arraytoxls;
  oci_close($ora_conn);
  $mysql_con->close();
}
else if($submit == "List Active-Status Items") {
  include_once 'header.php';
  echo "
  <h1>
    <img src='../img/sm-asi.png' alt='LSMS - List Active-Status Items'/>
  </h1>";

  $where = "";

  for($i = 0; $i < sizeof($sessionsArray); $i++) {
    if($i == 0) {
      $where = " SessionID = " . $sessionsArray[$i];
    }
    else {
      $where = $where . " or SessionID = " . $sessionsArray[$i];
    }
  }

  $sql = "SELECT * FROM
          (SELECT log_scanned_item.*, item_status.ITEM_STATUS_DESC FROM log_scanned_item LEFT JOIN item_status ON (log_scanned_item.ActiveStatusID = item_status.ITEM_STATUS_TYPE)) AS tmptbl WHERE (" . $where . ") AND (Status = 'A')";

  $result = $mysql_con->query ($sql) ;
  $col_count = mysqli_num_fields($result);

  echo "<table id='session-manager-tbl'>
  <thead>
  <tr>";

  for($col_num = 0; $col_num < $col_count; $col_num++) {
    $name_info = $result->fetch_field_direct($col_num);
    $field_name = $name_info->name;
    echo("  <th>$field_name</th>");
  }
  echo("  </thead></tr>");

  while($row = $result->fetch_array(MYSQLI_NUM)) {
    echo "<tbody><tr id='processed-items'>\n";
    for($col_num = 0; $col_num < $col_count; $col_num++) {
      echo "  <td>" . $row[$col_num] . "</td>\n";
    }
    echo "</tr></tbody>\n";
  }
  echo "</table>\n";
  $mysql_con->close();
}
else if($submit == "List Scanned Items") {
  include_once 'header.php';
  echo "
  <h1>
    <img src='../img/sm-si.png' alt='LSMS - List Scanned Items'/>
  </h1>";

  $where = "";

  for($i = 0; $i < sizeof($sessionsArray); $i++) {
    if($i == 0) {
      $where = " SessionID = " . $sessionsArray[$i];
    }
    else {
      $where = $where . " or SessionID = " . $sessionsArray[$i];
    }
  }

  $result = $mysql_con->query ("SELECT * FROM log_scanned_item WHERE $where ORDER BY SessionID, CheckDate");

  $col_count = mysqli_num_fields($result);

  echo "<a href='phpToExcel.php?filename=Scanned items'>Export to Excel</a>";
  echo "<table id='session-manager-tbl'>
  <thead>
  <tr>";

  for($col_num = 0; $col_num < $col_count; $col_num++) {
    $name_info = $result->fetch_field_direct($col_num);
    $field_name = $name_info->name;
    echo("  <th>$field_name</th>");
    $arraytoxls[1][] = $field_name;
  }
  echo("  </thead></tr>");

  while($row = $result->fetch_array(MYSQLI_NUM)) {
    echo "<tr id='processed-items'>\n";
    for($col_num = 0; $col_num < $col_count; $col_num++) {
      echo "  <td>" . $row[$col_num] . "</td>\n";
      $arraytoxls[$rowcounter][] = $row[$col_num];
    }
    echo "</tr>\n";
    $rowcounter++;
  }
  echo "</table>\n";
  $_SESSION['arraytoxls'] = $arraytoxls;
  $mysql_con->close();
}
else if($submit == "Delete") {

  for($i = 0; $i < sizeof($sessionsArray); $i++) {
      mysqli_query($mysql_con,"DELETE from session where SessionID = $sessionsArray[$i] ");
      mysqli_query($mysql_con,"DELETE from session_location where SessionID = $sessionsArray[$i] ");
      mysqli_query($mysql_con,"DELETE from log_not_on_shelf_item where SessionID = $sessionsArray[$i] ");
      mysqli_query($mysql_con,"DELETE from log_scanned_item where SessionID = $sessionsArray[$i] ");
  }

  $mysql_con->close();
  $url =  "$SYSTEM_BASE_URL/sessionmanager/index.php?fromSession=$fromSession&toSession=$toSession";
  header("Location: " . $url);


}

?>
</div>
</body>
