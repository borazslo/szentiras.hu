<?php

$sb="<span class='alap'>";
$se="</span>";

function listsubcats($db, $catdid) {
  updatecathit($db,$catdid);
  $recordset = $db->execute("select did, name, description, count, new from tdcat where reftop = $catdid order by ord, name");
  return $recordset;
}

function listcatsmod($db) {
  $recordset = $db->execute("select did, namehier from tdcat order by namehier");
  return $recordset;
}

function showcatsmod($rs,$scrcat) {
  if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
	do {
	  echo shln($rs->fields["namehier"],$scrcat . "?did=" . $rs->fields["did"],"catlink");
          echo "<br>\n" ;
	  $rs->nextRow();
	} while (!$rs->EOF);
  }
  $rs->close();
}

function showcats($rs,$scrcat) {
  global $sb, $se;
  if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
	do {
	  echo shln($rs->fields["name"],$scrcat . "?did=" . $rs->fields["did"],"catlinklarge");
          if (strlen(trim($rs->fields["description"]))>0) {
             echo " - $sb" . $rs->fields["description"] . $se;
          }
          echo "<i> (" . $rs->fields["count"] . ")</i>";
          if ($rs->fields["new"]<>0) {
                echo " <img src='/img/new.gif'>";
          }
          echo "<br>\n" ;
	  $rs->nextRow();
	} while (!$rs->EOF);
  }
  $rs->close();
}

function showcatshier($rs,$scrcat) {
  global $sb, $se;
  if ($rs->GetNumOfRows() > 0) {
     echo "<blockquote>";
     $rs->firstRow();
	do {
	  echo shln($rs->fields["namehier"],$scrcat . "?did=" . $rs->fields["did"],"catlinklarge");
          echo " - $sb " . $rs->fields["description"];
          echo "<i> (" . $rs->fields["count"] . ")</i>$e";
          if ($rs->fields["new"]<>0) {
                echo " <img src='/img/new.gif'>";
          }
          echo "<br>\n" ;
	  $rs->nextRow();
	} while (!$rs->EOF);
     echo "</blockquote>";
  }
  $rs->close();
}



function listlinksall($db, $catdid, $offset, $rows) {
  if (!empty($catdid)) {
    $rs1 = $db->execute("select count(*) as cnt from tdlink, tdlinktocat where tdlink.mod = 1 and tdlinktocat.reflink= tdlink.did and tdlinktocat.refcat = $catdid");
    $rs1->firstRow();
    $catcount = $rs1->fields["cnt"];
    $rs1->close();

    if ($catcount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$catcount) {$offset = ($catcount - ($catcount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       $querystring = "select tdlink.did as did, title, description, url, to_days(now()) - to_days(reccreated) as age from tdlink, tdlinktocat where tdlink.mod = 1 and tdlinktocat.reflink= tdlink.did and tdlinktocat.refcat = $catdid order by title limit $offset, $rows";

       $rs = $db->execute($querystring);
       return array($rs, $catcount, $offset, $rows);
    }
  }
}


function listlinkstopcat($db, $catdid, $rows) {
  if (!empty($catdid)) {
       if (empty($rows)) {$rows=10;}

       $querystring = "select tdlink.did as did, title, description, url, to_days(now()) - to_days(reccreated) as age from tdlink, tdlinktocat where tdlink.mod = 1 and tdlinktocat.reflink= tdlink.did and tdlinktocat.refcat = $catdid order by hit desc limit 0, $rows";

       $rs = $db->execute($querystring);
       return $rs;
  }
}


function listlinkstop($db, $offset, $rows) {
    $rs1 = $db->execute("select count(*) as cnt from tdlink where tdlink.mod = 1");
    $rs1->firstRow();
    $querycount = $rs1->fields["cnt"];
    $rs1->close();

    if ($querycount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$querycount) {$offset = ($querycount - ($querycount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       $querystring = "select did, title, description, url, hit from tdlink where tdlink.mod = 1 order by hit desc, title asc limit $offset, $rows";

       $rs = $db->execute($querystring);
       return array($rs, $querycount, $offset, $rows);
    }
}


function listcatstop($db, $offset, $rows) {
    $rs1 = $db->execute("select count(*) as cnt from tdcat");
    $rs1->firstRow();
    $querycount = $rs1->fields["cnt"];
    $rs1->close();

    if ($querycount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$querycount) {$offset = ($querycount - ($querycount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       if (empty($rows)) {$rows=10;}

       $querystring = "select did, namehier, count, hit from tdcat order by hit desc limit $offset, $rows";

       $rs = $db->execute($querystring);
       return array($rs, $querycount, $offset, $rows);
    }
}


function showlinks($rs,$script) {
  global $sb, $se;
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
            echo "<img src=/img/arrowright.jpg>&nbsp;";
	    echo shln($rs->fields["title"],$script . "?did=" . $rs->fields["did"] . "&url=http://". $rs->fields["url"],"link' target='_blank") . "\n";
            if (strlen(trim($rs->fields["description"]))>0) {
               echo " - $sb" . $rs->fields["description"] . $se . "\n";
            }
            if ($rs->fields["age"]<30) {
                echo " <img src='/img/new.gif'>";
            }
/*            echo "<a href=\"javascript:ujablak()\"><img src='/img/info.gif' border='0'></a>\n";*/
            echo "<a href=\"javascript:ujablak('" . $baseurl . "/keres/linkinfo.php?did=". $rs->fields["did"] . "')\"><img src='/img/info.gif' border='0'></a>\n";
            echo "<br>\n" ;
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
}


function showlinksnextprev($request, $catcount, $offset, $rows, $paramchr){

  echo "<br><br><table><tr>";

  if (!empty($request) && !empty($catcount)) {
     if (empty($offset)) {$offset=0;}
     if (empty($rows)) {$rows=50;}

     if ($offset > 0) {
         if ($offset > $rows) {
           $prevoffset = $offset - $rows;
         } else {
           $prevoffset = 0;
         }
         $prevrows = $rows;
         $prevstring = $prevoffset+$prevrows;
         $prevstring = (string) $prevstring;
         $prevstring = $prevoffset+1 ." - " . $prevstring;
         echo "<td align='left' width='100%'>";
         echo shln($prevstring , $request . $paramchr . "offset=$prevoffset&rows=$prevrows","link");
         echo "&nbsp;</td>";
     }

     if ($catcount > $offset+$rows) {
         $nextoffset = $offset + $rows;
         if ($catcount > $offset + 2*$rows) {
           $nextrows = $rows;
         } else {
           $nextrows = $catcount - $offset - $rows;
         }
         $nextstring = $nextoffset+$nextrows;
         $nextstring = (string) $nextstring;
         $nextstring = $nextoffset + 1 ." - " . $nextstring;
         echo "<td align='right'  width='100%'>&nbsp;";
         echo shln($nextstring , $request .  $paramchr . "offset=$nextoffset&rows=$rows","link");
         echo "</td>";
     }
     echo "</tr></table>";
  }
}

function quicksearchcats($db, $searchexp) {

  $recordset = $db->execute("select did, name, namehier, description, count, new from tdcat where name regexp '" . $searchexp . "' or description regexp '" . $searchexp . "' order by namehier");
  return array($recordset,$recordset->GetNumOfRows());
}


function quicksearchlinks($db, $searchexp, $offset, $rows) {
  if (!empty($searchexp)) {
    $searchexp=str_replace("_"," ",$searchexp);
    $rs1 = $db->execute("select count(*) as cnt from tdlink where tdlink.mod = 1 and (title regexp '" . $searchexp . "' or description regexp '" . $searchexp . "' or url regexp '". $searchexp . "')");
    $rs1->firstRow();
    $catcount = $rs1->fields["cnt"];
    $rs1->close();

    if ($catcount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$catcount) {$offset = ($catcount - ($catcount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       $querystring = "select did, title, description, url, to_days(now()) - to_days(reccreated) as age from tdlink where tdlink.mod = 1 and (title regexp '" . $searchexp . "' or description regexp '" . $searchexp . "' or url regexp '". $searchexp . "') order by title limit $offset, $rows";

       $rs = $db->execute($querystring);
       return array($rs, $catcount, $offset, $rows);
    }
  }
}

function advsearchlinks($db, $title, $url, $refdenom, $offset, $rows) {
   $wherestr="";
   if (!empty($title)) {
      $wherestr=  " (title regexp '" . $title . "' or description regexp '" . $title . "') ";
   }
   if(!empty($url)) {
      if (strlen($wherestr)>0) {
          $wherestr = $wherestr . " and url regexp '". $url . "' ";
      } else {
          $wherestr = " url regexp '". $url . "' ";
      }
   }
   if ($refdenom > 0) {
      if (strlen($wherestr)>0) {
          $wherestr = $wherestr . " and refdenom = $refdenom ";
      } else {
          $wherestr = " refdenom = $refdenom ";
      }
   }

   #echo "<br>" . $wherestr . "<br>";

   if (strlen($wherestr)>0) {
      $title=str_replace("_"," ",$title);
      $rs1 = $db->execute("select count(*) as cnt from tdlink where tdlink.mod = 1 and $wherestr");
      $rs1->firstRow();
      $catcount = $rs1->fields["cnt"];
      $rs1->close();

      if ($catcount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$catcount) {$offset = ($catcount - ($catcount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       $querystring = "select did, title, description, url, to_days(now()) - to_days(reccreated) as age from tdlink where tdlink.mod=1 and $wherestr order by title limit $offset, $rows";

       #echo "<br>" . $querystring . "<br>\n";

       $rs = $db->execute($querystring);
       return array($rs, $catcount, $offset, $rows);
      }
   }
}


function urlhit($db, $did) {
   updatelinkhit($db,$did);
   $querystring= "select url from tdlink where did=$did";
   $rs = $db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
         return $rs->fields["url"];
   }
   $rs->close;
}


function listlinksmod($db, $offset, $rows) {
    $rs1 = $db->execute("select count(*) as cnt from tdlink where tdlink.mod = 0");
    $rs1->firstRow();
    $catcount = $rs1->fields["cnt"];
    $rs1->close();

    if ($catcount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$catcount) {$offset = ($catcount - ($catcount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       $querystring = "select did, title, description, to_days(now()) - to_days(reccreated) as age from tdlink where tdlink.mod = 0 order by reccreated, title limit $offset, $rows";

       $rs = $db->execute($querystring);
       return array($rs, $catcount, $offset, $rows);
    }
}

function listdenoms($db) {
       $querystring = "select did, name from tddenom order by name";
       $rs = $db->execute($querystring);
       return $rs;
}


function listlangs($db) {
       $querystring = "select did, name from tdlang order by name";
       $rs = $db->execute($querystring);
       return $rs;
}

function listcathiers($db) {
       $querystring = "select did, namehier from tdcat order by namehier";
       $rs = $db->execute($querystring);
       return $rs;
}

function namehier ($db, $name, $reftop) {
      if ($reftop <= 0 ) {
            $tmpNamehier = $name;
      } else {
            $tmpNamehier = dlookup($db,"name", "tdcat", "did=" . $reftop) . " -> " . $name;
            $tmpReftop = dlookup($db,"reftop", "tdcat", "did=" . $reftop);
            while ($tmpReftop != 0) {
                $tmpNamehier = dlookup($db,"name", "tdcat", "did=" . $tmpReftop) . " -> " . $tmpNamehier;
                $tmpReftop = dlookup($db,"reftop", "tdcat", "did=" . $tmpReftop);
            }
      }
      return $tmpNamehier;
}

function shownamehier ($db, $did) {
      shln("Kategóriák","/keres","link");
      if ($did !=0) {
        $rs = $db->execute("select name, reftop from tdcat where did=$did");
        $rs->firstRow();
        $name = $rs->fields["name"];
        $reftop =$rs->fields["reftop"];
        $output = "<a href='/keres/showcat.php?did=" . $did . "' class='link'>$name</a>";
        $output = " <img src='/img/arrowright.jpg'> " . $output;
        while ($reftop != 0) {
            $rs = $db->execute("select did, name, reftop from tdcat where did=$reftop");
            $rs->firstRow();
            $did = $rs->fields["did"];
            $name = $rs->fields["name"];
            $reftop =$rs->fields["reftop"];
            $output = "<a href='/keres/showcat.php?did=" . $did . "' class='link'>$name</a>" . $output;
            $output = " <img src='/img/arrowright.jpg'> " . $output;
        }
        echo $output;
      }
}



function displaytextfield ($name,$size,$maxlength,$value,$comment,$class){
  echo "<span class='alap'>$comment</span><br><input type=text name='". $name ."' size=$size maxlength=$maxlength value='" . $value . "' class='" .$class."'><br>\n";
}


function displaytextarea ($name,$cols,$rows,$value,$comment,$class){
  echo "<span class='alap'>$comment</span><br><textarea name='" .$name ."' cols=$cols rows=$rows wrap class='" .$class."'>" . $value . "</textarea><br>\n";
}

function displayoptionlist($name,$size,$rs,$valuefield,$listfield,$default,$comment,$class){
  echo "<span class='alap'>$comment</span> <br>";
  echo "<select name='". $name . "' size='" . $size . "' class='" .$class."'>\n";
  if ($rs->GetNumOfRows() > 0) {
    $rs->firstRow();
    $i=1;
    do {
       echo "<option ";
       if ($i == 1 ) {
          if (empty($default)){echo "selected ";}
          echo "value ='-1'> - - - - - </option>\n";
       } else {
          if (!empty($default)){
            if ($default == $rs->fields[$valuefield]) {echo "selected ";}
          }
          echo "value ='" . $rs->fields[$valuefield] . "' class='" .$class."'>" . $rs->fields[$listfield] . "</option>\n";
          $rs->nextRow();
       }
       $i++;
    } while ((!$rs->EOF));
  }
  $rs->close;
  echo "</select><br>\n";
}

function displayboollist($name,$default,$comment,$class){
  echo "<span class='alap'>$comment</span> <br>";
  echo "<select name='". $name . "' size='2'>\n";
  if ($default == 1 ) {
       echo "<option selected value ='1' class='" .$class."'>Igen</option>";
       echo "<option value ='0'>Nem</option>";
  } else {
       echo "<option value ='1'>Igen</option>";
       echo "<option selected value ='0' class='" .$class."'>Nem</option>";
  }
  echo "</select><br>\n";
}

function displaycheckbox($name,$value,$default,$comment){
 echo "<span class='alap'>$comment:</span> ";
 echo "<input type='checkbox' name='". $name ."' value='" . $value . "'";
 if ($default==1){ echo " checked";}
 echo ">";
}

function displayhidden($name,$value){
  echo "<input type=hidden name='" . $name . "' value= '" . $value . "'><br>\n";
}


function checkdata ($data, $length, $text) {
  if (!empty($data)) {
     if (strlen($data) <= $length) {
        $ok = 1;
     } else {
        $ok = 0;
        echo "<p class='hiba'> Túl hosszú a " . $text . "!\n";
        echo "Maximum " . $length . " karakter. </p>\n";
     }
  } else {
     $ok = 0;
     echo "<p class='hiba'> Hiányzik a " . $text . "!</p>\n";
  }
  return $ok;
}

function checkurl ($db, $url) {
  $did = dlookup($db,"did","tdlink","url = '" . $url . "'");
  if ($did > 0) {
    echo "<p class='hiba'> Ez a cím már szerepel az adatbázisban!</p>";
    return 0;
  } else {return 1; }
}

function checkoption ($data, $text) {
  if (!empty($data)) {
     if ($data > 0) {
        $ok = 1;
     } else {
        $ok = 0;
        echo "<p class='hiba'> Nem választott értéket a következõhöz: " . $text . "!</p>\n";
     }
  } else {
     $ok = 0;
     echo "<p class='hiba'> Hiányzik a " . $text . "!</p>\n";
  }
  return $ok;
}


function getlinkdata($db, $condition) {
   $querystring= "select * from tdlink where $condition";
   $rs = $db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
         for ($i=0; $i < $rs->GetNumOfFields(); $i++) {
           $res[$i] = $rs->fields[$i];
         }
         return $res;
   }
   $rs->close;
}

function getcatdata($db, $condition) {
   $querystring= "select * from tdcat where $condition";
   $rs = $db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
         for ($i=0; $i < $rs->GetNumOfFields(); $i++) {
           $res[$i] = $rs->fields[$i];
         }
         return $res;
   }
   $rs->close;
}

function getcatlist($db,$linkdid) {
   $querystring= "select did, refcat from tdlinktocat where reflink = $linkdid";
   $rs = $db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
         for ($i=0; !$rs->EOF; $i++) {
           $res1[$i] = $rs->fields["did"];
           $res2[$i] = $rs->fields["refcat"];
           $rs->nextRow();
         }
         $res= array ($res1,$res2);
         return $res;
   }
   $rs->close;
}

function getdenom($db, $did){
  return dlookup($db, "name","tddenom","did=$did");
}

function getnamehier($db, $did){
  return dlookup($db, "namehier","tdcat","did=$did");
}

function dlookup($db, $field,$table,$condition) {
   $querystring= "select $field from $table where $condition";
   $rs = $db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
         return $rs->fields[$field];
   } else {
      return 0;
   }
   $rs->close;
}

function insertdata($db, $table, $fieldlist, $valuelist) {
   $querystring= "insert into " . $table . " (" . $fieldlist . ") values (" . $valuelist . ")";
   $result = $db->execute($querystring);
   if (mysql_errno() !=0) {
      echo "<p class='hiba'> Hiba az insertdata függvényben!<br> " . mysql_errno().": ".mysql_error()."</p>";
      echo "<p class='hiba'> SQL: " . $querystring . "</p>";
   }
   return mysql_errno();
}

function updatedata($db, $table, $field, $value, $condition) {
   $querystring= "update " . $table . " set " . $field . " = " . $value . " where " . $condition;
   /* echo $querystring . "<br>"; */
   $result = $db->execute($querystring);
   if (mysql_errno() !=0) {
      echo "<p class='hiba'> Hiba az updatedata függvényben!<br>" . mysql_errno().": ".mysql_error()."</p>";
      echo "<p class='hiba'> SQL: " . $querystring . "</p>";
   }
   return mysql_errno();
}


function deletedata($db, $table, $condition) {
  if (!empty($condition)) {
   $querystring= "delete from " . $table . " where " . $condition;
   $result = $db->execute($querystring);
   if (mysql_errno() !=0) {
      echo "<p class='hiba'> Hiba az insertdata függvényben!<br>" . mysql_errno().": ".mysql_error()."</p>";
      echo "<p class='hiba'> SQL: " . $querystring . "</p>";
   }
   return mysql_errno();
  } else {
   return 1;
  }
}

function tdate($timestamp) {
  if ($timestamp > 0) {
    return substr($timestamp,0,4) . "/" . substr($timestamp,4,2) . "/" . substr($timestamp,6,2);
  } else {
    return "0";
  }
}


function shln($name,$url,$class) {

  echo "<a href='" . $url . "' class='" . $class . "'>" . $name . "</a>";

}

function updatecathit($db,$did) {
  $oldhit = dlookup($db,"hit","tdcat","did=".$did);
  updatedata($db,"tdcat","hit",$oldhit+1,"did=".$did);
}

function updatelinkhit($db,$did) {
  $oldhit = dlookup($db,"hit","tdlink","did=".$did);
  updatedata($db,"tdlink","hit",$oldhit+1,"did=".$did);
}

/*if ($rs->GetNumOfRows() > 0) {
    $rs->firstRow();
    do {
       echo $rs->fields["name"] . "<br>\n";
       $rs->nextRow();
   } while ((!$rs->EOF));
 } */




?>