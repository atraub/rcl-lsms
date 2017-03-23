<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * Update Location
 *
 * This file is called when the location manager is used.
 * This will make changes to the location table.
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

// Config File
include_once '../shared/Config.php';

// MySQL connect
$mysql_con = new mysqli($MYSQL_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
if ($mysql_con->connect_error) {
  trigger_error('Database connection failed: '  . $mysql_con->connect_error, E_USER_ERROR);
}

$locationCode = $_POST['locationcode'];
$locationName = $_POST['locationname'];

mysqli_query ($mysql_con,"INSERT INTO location VALUES ('$locationCode', '$locationName')");

$mysql_con->close();
