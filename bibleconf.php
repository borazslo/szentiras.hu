<?php
/* General constants */
//$fileurl = "http://www.kereszteny.hu/biblia2/";
//$fileurl = "http://szentiras.hu/biblia2/";
//$baseurl = "http://szentiras.hu/";

$fileurl = "http://szentiras.hu/";
$baseurl = "http://szentiras.hu/";

$pagetitle = 'Szentírás';
$content = '';
$meta = '';
$share = '';
$comments = '';
$title = false;
$tipps = array();	

/* Database */
require("include/phpDB/phpdb.inc");
$dbconndie = "Nem sikerült kapcsolódni az adatbázishoz: 1";
$db = new phpDB();
$db->pconnect("localhost", "szentiras", "saritnezs11") or die($dbconndie);
$db->execute("SET NAMES 'utf8'");
$db->execute("SET CHARACTER SET 'utf8'");
$db->selectDB("bible");

?>
