<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * Update Parameter
 *
 * This file is called when scanning books and you want to change the
 * parameters of the institution.  Parameters include the barcode length
 * and the prefix
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

$barlength = $_POST['barlength'];
$prefix = $_POST['prefix'];

mysqli_query ($mysql_con,"UPDATE parameter SET  barlength = '$barlength', barprefix = '$prefix' where libcode = '$LIBRARY_CODE';");

$mysql_con->close();

echo "Parameters successfully updated";
