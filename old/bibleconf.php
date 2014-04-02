<?php

define(FILE, getenv("DOCUMENT_ROOT")."/");
define(BASE, "http://".getenv("HTTP_HOST")."/");
$baseurl = BASE;
$fileurl = $baseurl;

define('DBPREF','kar_'); // prefixes of the tables in the database
define('DROPBOXF','Bibliafordítások'); // A dropbox foldere a megosztottaknak

$sitetitle = 'Szentírás <sup>v3</sup>';
$subsitetitle = 'Katolikus Biblia fordítások az interneten';
$content = '';
$meta = '';
$share = '';
$comments = '';
$title = false;
$tipps = array();	
$dbHost = 'localhost';
$dbName = 'bible';
$dbPassword = getenv('MYSQL_SZENTIRAS_PASSWORD');
$dbUser = getenv('MYSQL_SZENTIRAS_USER');

try { 
        $db = new PDO("mysql:host=${dbhost};dbname=${dbName}", $dbUser, $dbPassword, array('charset'=>'UTF-8',PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $exception) { 
	echo '<strong>Végzetes hiba törént!</strong>';
        echo "<br/>Nem sikerült az adatbázishoz kapcsolódni! ${dbHost}:${dbName}";
	echo '<br/>Részletek: '. $exception->getMessage(); 
   exit; 
} 

/**/
$copyright = "
<p><!-- &copy; <a href='http://www.kereszteny.hu/mkie' class='menulink'>MKIE</a> - 
				<a href='http://www.oki-iroda.hu' class='menulink'>ÖKI</a> - 
				<a href='http://kim.katolikus.hu' class='menulink'>KIM</a>
				 2001-2010,<br>-->
				 &copy; <a href='http://www.eleklaszlo.hu' class='menulink'>Elek László SJ</a> (<a href='http://jezsuita.hu' alt='Jézus Társasága Magyarországi Rendtartománya'>JTMR</a>) 2013-2014.<br>
				 Minden jog fenntartva.</p>";
?>
