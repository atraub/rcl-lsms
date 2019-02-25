<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
 
/**
 * Session Manager
 *
 * Outputs the back-end interface.  User has the
 * option to delete sessions, list shelf items
 * and process items.
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
 
// Config Files
include_once '../shared/Config.php';
// Authentication File
include_once '../shared/Auth.php';
error_reporting (0);
// Collect parameters
if(isset($_GET["fromSession"])) {
  $fromSession = $_GET["fromSession"];
} else {
  $fromSession = 1;
}
if(isset($_GET["toSession"])) {
  $toSession = $_GET["toSession"];
} else {
  $toSession = "20";
}
// Used for excel exporting
$arraytoxls = array( 1=> array());
$rowcounter = 2;
// If from session isn't specified, we can assume that the user just wants to
// see the default page.  Instead of typing '1' in the from, then clicking go,
// they will automatically see the last 20 sessions
if($fromSession == "") {
  
  $mysql_query = "SELECT SessionID FROM session ORDER BY sessionid DESC LIMIT 1;";
  
  $mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
  if ($mysql_con->connect_error) {
    trigger_error('Database connection failed: ' . $mysql_con->connect_error, E_USER_ERROR);
  }
  
  /*----
  $mysql_query = "SELECT * FROM session WHERE (SessionID >= " . $fromSession . ") and (SessionID <= " . $toSession . ") ORDER by SessionID";
  if($mysql_query != "") {
  $mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
  if ($mysql_con->connect_error) {
    trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
  }
  $result = $mysql_con->query($mysql_query);
  ----*/
  
  $result = $mysql_query->query($mysql_query);
  $row = $result->fetch_array(MYSQLI_ASSOC);
  $lastsession = $row['sessionid'];
  $fromSession = ($lastsession - 50);
  if($fromSession < 0) { $fromSession = 1; }
  header("Location: index.php?fromSession=$fromSession&toSession=");
  $mysql_con->close();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1">
  <title>LSMS - Session Manager</title>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=PT+Sans+Caption:700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="../css/reset.css" type="text/css">
  <link rel="stylesheet" href="../css/lsms.css" type="text/css">
</head>

<body>

<div id="nav">
  <ul>
    <li><a href="../index.php">LSMS Home</a></li>
    <li><a href="#">Session Manager</a></li>
    <li><a href="../locationmanager">Location Manager</a></li>
  </ul>
</div>

<div id="container">
<h1>
  <img src="../img/title-sm.png" alt="LSMS - Session Manager"/>
</h1>

<div id="session-range">
  <form action = "" type=get>
    <p>Session Filter by <strong>ID</strong> - From: <input name='fromSession' size='4' value='<?php echo $fromSession; ?>'>  To: <input name='toSession' size='4' value='<?php echo $toSession; ?>'> <input type=submit value=Go></p>
  </form>
</div>
<?php
$mysql_query ="" ;
// If a beginning/end sessions are specified, it will
// generate a query to display on the page
if(($fromSession != "") && ($toSession != "")) {
  $mysql_query = "SELECT * FROM session WHERE (SessionID >= " . $fromSession . ") and (SessionID <= " . $toSession . ") ORDER by SessionID";
}
else if(($fromSession == "") && ($toSession != "")) {
  $mysql_query = "SELECT * FROM session WHERE SessionID <= " . $toSession . " ORDER by SessionID";
}
else if(($fromSession <> "") && ($toSession == "")) {
  $mysql_query = "SELECT * FROM session WHERE SessionID >= " . $fromSession . " ORDER by SessionID";
}
if($mysql_query != "") {
  $mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
  if ($mysql_con->connect_error) {
    trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
  }
  $result = $mysql_con->query($mysql_query);
  /*
      <font>
      <strong>ID</strong>: Session ID;
      <strong>Not-On-Shelf</strong>: Time when creating not-on-shelf items;
      <strong>TS</strong>: Total Scan; <strong>VS</strong>: Valid Scan;
      <strong>SH</strong>: Items in Voyager - all;
      <strong>AC</strong>: Items in Voyager with active status;
      <strong>NS</strong>: Not-On-Shelf-Items for the session
    </font>
    */
  echo("
  <form name=form1 action='processSessions.php' method=get onsubmit='return validate();'>
    <input type=submit name=submit value='List Not-On-Shelf items'>
    <input type=submit name=submit value='List Not-On-Shelf items with Title'>
    <input type=submit name=submit value='List Active-Status Items'>
    <input type=submit name=submit value='List Scanned Items'>
    <input type=submit name=submit value=Delete onclick='confirmDelete=1;'>
    <input type=button value='Reset' onclick='location.reload();'>
    <a href='phpToExcel.php?filename=Sessionmanager list'>Export to Excel</a>
    <br />
    <table id='session-manager-tbl'>
    <thead>
    <tr>
      <th style='cursor: help' title='Session ID'>ID</th>
      <th>Loc</th>
      <th>Session Start</th>
      <th>Session End</th>
      <th>Start Callnum</th>
      <th>End Callnum</th>
      <th>Start<br />Barcode</th>
      <th>End<br />Barcode</th>
      <th style='cursor: help' title='Time when creating not-on-shelf items'>Not-On-Shelf</th>
      <th style='cursor: help' title='Total Scan'>TS</th>
      <th style='cursor: help' title='Valid Scan'>VS</th>
      <th style='cursor: help' title='Items in Voyager - all'>SH</th>
      <th style='cursor: help' title='Items in Voyager with active status'>AC</th>
      <th style='cursor: help' title='Not-On-Shelf-Items for the session'>NS</th>
    </tr>
    </thead>
    <tbody>\n");
  $arraytoxls[1] = array("Session ID", "Loc", "Session Start", "Session End", "Start Callnum", "End Callnum", "Start Barcode", "End Barcode", "Not-on-shelf", "TS", "VS", "SH", "AC", "NS");
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $locationcode_query = $mysql_con->query("SELECT * FROM session_location WHERE SessionID = " . $row['SessionID']);
    $count = 0;
    while ($subRow = $locationcode_query->fetch_array(MYSQLI_ASSOC)) {
      if ($count == 0) {
        $locationCode =  $subRow["LocationCode"];
      }
      else {
        $locationCode =  $locationCode . ";" .$subRow["LocationCode"];
      }
      $count++;
    }
  echo("
  <tr>
    <td nowrap>" . $row['SessionID'] . "<input type=checkbox name=SessionID value=" . $row['SessionID'] . "></td>
    <td nowrap>" . $locationCode . "</td>
    <td nowrap>" . $row['Start_Time'] . "</td>
    <td nowrap>" . $row['End_Time'] . "</td>
    <td nowrap>" . $row['Start_N_Callnum'] . "</td>
    <td nowrap>" . $row['End_N_Callnum'] . "</td>
    <td nowrap>" . $row['Start_Barcode'] . "</td>
    <td nowrap>" . $row['End_Barcode'] . "</td>
  ");
  $urlRevive   = "javascript:location.href='" . $SYSTEM_BASE_URL . "/sessionmanager/reviveSession.php?fromSession=$fromSession&toSession=$toSession&sessionID=" . $row['SessionID'] . "';";
  $urlGenerate = "javascript:location.href='" . $SYSTEM_BASE_URL . "/sessionmanager/processNotOnShelfItemsSilent.php?fromSession=$fromSession&toSession=$toSession&sessionID=" . $row["SessionID"] . "';";
  $urlDelete   = "javascript:location.href='" . $SYSTEM_BASE_URL . "/sessionmanager/deleteSession.php?sessionID=" . $row["SessionID"] . "';";
  echo("  <td nowrap>");
  if ((($row["End_N_Callnum"]== "undefined") ||
       ($row["End_N_Callnum"]==null) ||
       ($row["Start_N_Callnum"]== "undefined") ||
       (!$row["Start_N_Callnum"])) &&
      ($row["Start_Barcode"]) &&
      ($row["End_Barcode"])) {
    echo("<input type='button' value='Revive' onclick=\"" . $urlRevive . "\" >");
  }
  if (((!$row["TimeNotOnShelf"]) &&
       ($row["Start_N_Callnum"]) &&
       ($row["Start_N_Callnum"] != "undefined") &&
       ($row["End_N_Callnum"]) &&
       ($row["End_N_Callnum"] != "undefined")) ||
      ($row["TimeNotOnShelf"] < $row["End_Time"])) {
    echo("<input type='button' value='Generate' onclick=\"" .  $urlGenerate . "\" >");
  }
  else {
    echo($row["TimeNotOnShelf"]);
  }
  echo("      </td>\n");
  echo("        <td>" . $row["TotScan"] . "</td>
    <td>" . $row["ValScan"] . "</td>
    <td>" . $row["TotShelfList"] . "</td>
    <td>" . $row["TotActive"] . "</td>
    <td>" . $row["ItemsNotOnShelf"] .  "</td>
  </tr>\n");
    $arraytoxls[$rowcounter] = array($row['SessionID'],$locationCode,$row['Start_Time'],$row['End_Time'],$row['Start_N_Callnum'],$row['End_N_Callnum'],$row['Start_Barcode'],$row['End_Barcode'],$row["TimeNotOnShelf"],$row['TotScan'],$row['ValScan'],$row['TotShelfList'],$row['TotActive'],$row['ItemsNotOnShelf'],);
    $rowcounter++;
    $_SESSION['arraytoxls'] = $arraytoxls;
  } // end while
  echo("    </tbody></Table>\n
  <input type=submit name=submit value='List Not-On-Shelf items'>
  <input type=submit name=submit value='List Not-On-Shelf items with Title'>
  <input type=submit name=submit value='List Active-Status Items'>
  <input type=submit name=submit value='List Scanned Items'>
  <input type=submit name=submit value=Delete onclick='confirmDelete=1;'>
  <input type=reset value='Reset'><br />
  <input type=hidden name=fromSession value=" . $fromSession . ">
  <input type=hidden name=toSession value=" . $toSession . ">
  </form>");
  $mysql_con->close();
}
?>

<div id="footer">
  <p>*The last 50 sessions are being displayed by default.</p>

</div>
</div>
</body>

<script language=javascript>
var confirmDelete = 0;
function validate() {
  if (confirmDelete == 1) {
    var answer = confirm("This will DELETE the selected session(s)!");
    if (!answer) {
      confirmDelete = 0;
      return false;
    }
    else {
      confirmDelete = 0;
      return true;
    }
  }
}
</script>
</html>
