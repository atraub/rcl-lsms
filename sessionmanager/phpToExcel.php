<?php
require ("php-excel.class.php");
session_start();

$array = $_SESSION['arraytoxls'];
$filename = $_GET['filename'];

$xls = new Excel_XML;
$xls->addArray ( $array );
$xls->generateXML ( $filename );
?>
