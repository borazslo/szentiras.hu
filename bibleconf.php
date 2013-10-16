<?php
/* General constants */
//$fileurl = "http://www.kereszteny.hu/biblia2/";
//$fileurl = "http://szentiras.hu/biblia2/";
//$baseurl = "http://szentiras.hu/";

$fileurl = "http://szentiras.hu/";
$baseurl = "http://szentiras.hu/";

$sitetitle = 'Szentírás';
$subsitetitle = 'Magyar Keresztény Portál';
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

/**/
$copyright = "
<p>&copy; <a href='http://www.kereszteny.hu/mkie' class='menulink'>MKIE</a> - 
				<a href='http://www.oki-iroda.hu' class='menulink'>ÖKI</a> - 
				<a href='http://kim.katolikus.hu' class='menulink'>KIM</a>
				 2001-2010,<br>
				 &copy; <a href='http://www.eleklaszlo.hu' class='menulink'>Elek László SJ</a> 2013.<br>
				 Minden jog fenntartva.</p>";
?>
