<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * Location Manager
 *
 * This file is responsible for handling location options of the system.
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

// Config File
include_once '../shared/Config.php';
// Authentication File
include_once '../shared/Auth.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1">
  <title>LSMS - Session Manager</title>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="../css/reset.css" type="text/css">
  <link rel="stylesheet" href="../css/lsms.css" type="text/css">
  <script src="../js/jquery.js" type="text/javascript"></script>
  <script>
  $(document).ready(function() {
    $("#middle-btn-left").click(function() {
      var temp = $('[name="voy-select"]').val();
      var locations = temp.join("~~")
      var location_array = locations.split("~~");
      for(var i=0; i < location_array.length; i++) {
        locationdata = location_array[i].split("|");
        locationcode = locationdata[0];
        if(locationdata[1] == undefined ) {
          locationname = "";
        }
        else {
          locationname = locationdata[1];
        }

        $('[name="lsms-select"]')
        .append($('<option>', { value : locationcode + "|" + locationname })
        .text(locationcode + " - " + locationname));

        $.ajax({
          type: "POST",
          url: "../ajax/updateLocation.php",
          data: { locationcode: locationcode, locationname: locationname }
        });
      }

      $('[name="voy-select"] :selected').remove();
    });

    $("#middle-btn-right").click(function() {
      var temp = $('[name="lsms-select"]').val();
      var locations = temp.join("~~")
      var location_array = locations.split("~~");
      for(var i=0; i < location_array.length; i++) {
        locationdata = location_array[i].split("|");
        locationcode = locationdata[0];
        if(locationdata[1] == undefined ) {
          locationname = "";
        }
        else {
          locationname = locationdata[1];
        }

        $('[name="voy-select"]')
        .append($('<option>', { value : locationcode + "|" + locationname })
        .text(locationcode + " - " + locationname));

        $.ajax({
          type: "POST",
          url: "../ajax/removeLocation.php",
          data: { locationcode: locationcode, locationname: locationname }
        });
      }

      $('[name="lsms-select"] :selected').remove();

    });
  });
  </script>
</head>

<body>
<div id="nav">
  <ul>
    <li><a href="../index.php">LSMS Home</a></li>
    <li><a href="../sessionmanager">Session Manager</a></li>
    <li><a href="#">Location Manager</a></li>
  </ul>
</div>
<div id="container">
<h1>LSMS - Location Manager</h1>
<p>The table on the left are the locations that will be managed in the Library Stacks Management System.  The table on
   the right are the locations found in the Voyager Location table.  Select the locations you want to add from the right then
   click on the " < " button to add them to the system.  Multiple selections are possible by holding down the Control
    button while clicking locations.  If you with to remove a location from the left table, repeat the above and click the
    " > " button instead.</p>
<p><em>*** All actions are updated live, so just close the window when you are done making your changes.</em></p>
<div class="location-manager-select">
<h4>Locations managed by LSMS</h4>
<select multiple='multiple' name='lsms-select' style='width:100%; height:100%' id='location-manager-left'>
<?php
  $mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
  if ($mysql_con->connect_error) {
    trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
  }

  $lsms_array = array();
  $sql = "SELECT * FROM location ORDER BY Location_ID";
  $result = $mysql_con->query($sql);

  if($result === false){
    trigger_error('Wrong SQL: ' . $sql . 'Error: ' . $conn->error, E_USER_ERROR);
    echo "nothing in table";
  } else {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $location_code = $row['Location_ID'];
      $location_name = $row['Location_Name'];
      array_push($lsms_array, $location_code);
      echo "  <option value=\"$location_code|$location_name\">$location_code - $location_name</option>\n";
    }
  }
  $mysql_con->close();
?>
</select>
</div>

<div id="location-manager-middle-btns">
<button id="middle-btn-left"> < </button><br />
<button id="middle-btn-right">  > </button>
</div>

<div class="clear"></div>

<div class="location-manager-select">
<h4>Locations found in Voyager Location table</h4>
<select multiple='multiple' name='voy-select' style='width: 100%; height:100%' id='location-manager-left'>
<?php
$ora_conn = oci_connect($ORA_USERNAME, $ORA_PASSWORD, $ORA_CONNECTION);
if (!$ora_conn) {
  $e = oci_error();
  trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$sql = "
  SELECT
    distinct(location_code),
    location_display_name
  FROM
    $LIBRARY_CODE.location
  ORDER BY
    location_code";
$stid = oci_parse($ora_conn, $sql);
oci_execute($stid);
while ($voyagerRow = oci_fetch_array($stid , OCI_RETURN_NULLS)) {
  $location_code = $voyagerRow['LOCATION_CODE'];
  $location_display_name = $voyagerRow['LOCATION_DISPLAY_NAME'];
  if(!in_array($location_code, $lsms_array)) {
    echo "  <option value=\"$location_code|$location_display_name\">$location_code - $location_display_name</option>\n";
  }

}
?>
</select>
</div>


</div>

</body>
</html>
