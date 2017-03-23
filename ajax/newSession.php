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
 * This file is called when the user starts a new
 * scanning session.
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

// Inserting session into session table
mysqli_query($mysql_con,"INSERT INTO session (start_time) VALUES (now())");
$sessionID = $mysql_con->insert_id;  //ID for just inserted record

$result = $mysql_con->query("SELECT * FROM parameter WHERE LibCode ='$LIBRARY_CODE'");
$row = $result->fetch_array(MYSQLI_ASSOC);
$bar = $row['BarLength'];
$pre = $row['BarPrefix'];

echo (mysqli_error($mysql_con).'~'.$sessionID.'~'.$bar.'~'.$pre.'~'.$ORA_USERNAME.'~'.$ORA_PASSWORD.'~'.$LIBRARY_CODE);
$mysql_con->close();

?>
