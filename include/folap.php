<?php

require("phpdb.inc");
$dbconndie = "Nem sikerült kapcsolódni az adatbázishoz";
$db = new phpDB();
$db->connect("localhost:/var/run/mysqld/mysql.sock", "root", "") or die($dbconndie);

#Lapszemle functions

function listlastreviews($db) {
    $db->selectDB("szemle");
    $tb = "lapsz".date("Y");
    $rs1 = $db->execute("select max(datum) as maxdat from $tb");
    $rs1->firstRow();
    $maxdat = $rs1->fields["maxdat"];
    $rs1->close();

    if (!empty($maxdat)) {

       $querystring = "select LID, ujsag.ujsag as ujsnev, cim from $tb left join ujsag on $tb.ujsag=ujsag.UID where $tb.datum='".$maxdat."' and ujsag.sorrend>=10 group by ujsnev order by sorrend limit 0,5";
       $rs = $db->execute($querystring);
       return $rs;
    }
}

function listlastnews($db) {
    $db->selectDB("news");
    $tb = "lapsz".date("Y");
    $rs1 = $db->execute("select max(datum) as maxdat from $tb");
    $rs1->firstRow();
    $maxdat = $rs1->fields["maxdat"];
    $rs1->close();

    if (!empty($maxdat)) {

       $querystring = "select LID, ujsag.ujsag as ujsnev, cim from $tb left join ujsag on $tb.ujsag=ujsag.UID order by LID desc limit 0,5";
       $rs = $db->execute($querystring);
       return $rs;
    }
}


function showreviewslist($rs,$script) {
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
	    echo "<a href='". $script . "?type=cikk&e=".date("Y")."&LID=" . $rs->fields["LID"] ."' class='link'>".$rs->fields["cim"]."</a><br>&nbsp;&nbsp;<span class='alap'>(" . $rs->fields["ujsnev"].")</span><br><br>\n";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
}

#Hirek functions

function listnews($db) {
     $db->selectDB("kportal");
     $rs = $db->execute("select id,cim from hirek where ok='i' and jog='' order by bekulddatum desc limit 0,4");
    return $rs;
}

function shownewslist($rs,$script) {
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
            echo "<a href='". $script . "?op=view&hid=". $rs->fields["id"]. "' class='link'> ". $rs->fields["cim"]. "</a><br><br>";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
}




function inchtml ($dir, $filename) {
  $fp = fopen($dir . "/" . $filename, "r");
  while ($buffer = fgets($fp,4096)) {
    if (eregi("<!--#include virtual=",$buffer)) {
	$pieces = explode("\"", $buffer);
	inchtml($dir, $pieces[1]);
    } else {
    echo $buffer;
    }
  }
}

function listlastlinks($db) {
  $db->selectDB("linkdb");
  $rs = $db->execute("select did, title, url from tdlink where mod=1 order by reccreated desc limit 0,4");
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
	    echo "<a href=/keres/openlink.php?did=" . $rs->fields["did"] . "&url=http://".$rs->fields["url"] ." class=catlinksmall> - " . $rs->fields["title"] . "</a><br>";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
}

?>
