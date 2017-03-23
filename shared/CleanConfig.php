<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package   LSMS
 * @author    Nackil Sung, Erin Kim
 * @since   Version 1.0
 */

/**
 * LSMS configuration file
 *
 * This file contains global variables for configuration
 *
 * @package   LSMS
 * @author    Nackil Sung, Erin Kim
 * @since   Version 1.0
 */

/*
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
| $SYSTEM_BASE_URL The base URL where the system was installed in, excluding the trailing slash, '/'.
|                     - eg. http://library.state.edu/LSMS
| $LSMS_USERNAME   The username to sign into any LSMS.  This will only be required once per session
| $LSMS_PASSWORD   The password to sign into any LSMS.  This will only be required once per session
| $ORA_HOME        The installation directory of Oracle with the 'ORACLE_HOME=' prefix
|                     - eg. ORACLE_HOME=/oracle/app/oracle/product/10.2.0/db_1
| $ORA_USERNAME    The username to connect to the oracle database
| $ORA_PASSWORD    The password to connect to the oracle database
| $ORA_CONNECTION  Contains the Oracle instance to connect to
|                     - It can be an Easy Connect string, or a Connect
|                       Name from the tnsnames.ora file, or the name of
|                       a local Oracle instance.
|                     - host_name[:port][/service_name]
|                     - eg. library.state.edu/VGER.library.state.edu
| $MYSQL_SERVER    The MySQL server. It can also include a port number. e.g. "hostname:port"
| $MYSQL_USERNAME  The username to connect to the mysql database
| $MYSQL_PASSWORD  The password to connect to the mysql database
| $MYSQL_DATABASE  The database the LSMS is using
| $LIBRARY_CODE    The Library database prefix for the voyager database tables.
|                     -eg. the $LIBRARY_CODE in the following:
|                          $LIBRARY_CODE.mfhd
|                          $LIBRARY_CODE.item
|                          $LIBRARY_CODE.location
|                          $LIBRARY_CODE.view
| -------------------------------------------------------------------
*/


// -- Please fill in variables below --

$SYSTEM_BASE_URL = "http://rcldrupaldev.lib.rochester.edu:81/lsms";

$LSMS_USERNAME = "";
$LSMS_PASSWORD = "";

// Oracale Connection Variables
$ORA_HOME       = "";
$ORA_USERNAME   = "";
$ORA_PASSWORD   = "";
$ORA_CONNECTION = "";
putenv($ORA_HOME);




// !!! DO NOT EDIT BELOW THIS LINE !!!
// -- Variables generated from install script --
// MySQL Connection Variables
$MYSQL_SERVER   = "";
$MYSQL_USERNAME = "";
$MYSQL_PASSWORD = "";
$MYSQL_DATABASE = "";

// Library Database Prefix
$LIBRARY_CODE = ";
