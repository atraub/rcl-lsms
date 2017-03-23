<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * Auth
 *
 * This file will make checks to see if there is a
 * valid authenticated session for the user
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */
 
// Config File
include_once 'Config.php';

session_start();

$cur_page = get_page_URL();
if((!isset($_SESSION['authd'])) && (stripos($cur_page, "login") === false)) {
  $_SESSION['destination_page'] = $cur_page;
  header("Location: $SYSTEM_BASE_URL/login");
}

function get_page_URL() {
  $pageURL = 'http';

  $pageURL .= "://";
  
  if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } 
  else {
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
  
  return $pageURL;
}
