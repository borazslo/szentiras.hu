<?php

$sb="<span class='alap'>";
$se="</span>";

$translations = db_query('SELECT * FROM tdtrans');

function listbible($db) {
  $rs = $db->execute("select did, name, denom from tdtrans order by name");
  return $rs;
}

function listtrans($db, $reftrans) {
  $rs = $db->execute("select name, abbrev, oldtest from tdbook where reftrans = $reftrans order by bookorder");
  return $rs;
}

function listbook($db, $reftrans, $abbook) {
  $rs = $db->execute("select numch from tdverse where reftrans = $reftrans and abbook='". $abbook . "' group by numch order by numch");
  return $rs;
}

function listchapter($db, $reftrans, $abbook, $numch) {
  $rs = $db->execute("select did, numv, title, verse, refs from tdverse where reftrans = $reftrans and abbook='". $abbook . "' and numch=$numch order by did");
  return array($reftrans, $abbook, $numch, $rs);
}


function listcomm($db,$rs,$reftrans) {
    if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
        $rsmin=$rs->fields["did"];
        $rs->lastRow();
        $rsmax=$rs->fields["did"];
		$query = "select * from tdcomm where not (refbvers>" . $rsmax . " or refevers<" . $rsmin . ") and reftrans =" . $reftrans . " order by did";
		$rs2 = $db->execute($query);
        return array($rs2, $rsmin, $rsmax);
    }
}

function showbible($db, $rs) {
    global $baseurl;
	global $title;
	
	$return = '';
    if ($rs->GetNumOfRows() > 0) {
        $title = showhier($db, "", "", "");
        $return .= "<blockquote>";
        $rs->firstRow();
	 do {
            $return .= "<a href='" . $baseurl . "index.php?q=showtrans&reftrans=" . $rs->fields["did"] . "' class='link'>" . $rs->fields["name"] . " (" . $rs->fields["denom"] . ")</a></p>\n";
            $rs->nextRow();
	 } while (!$rs->EOF);
        $return .= "</blockquote>";
    }
	return $return;
}

function showtrans($db, $reftrans, $rs) {
    global $baseurl,$sb,$se,$title;
	$return = false;
    if ($rs->GetNumOfRows() > 0) {
		
        $title = showhier($db, $reftrans, "", "");
        $return .= showbookabbrevlist($db,$reftrans,"");
        #echo "<blockquote>";
        $return .= "<p class='kiscim'>Ószövetség</p>";
        $oldtest=1;
        $rs->firstRow();
	 do {
            if ($rs->fields["oldtest"]==0 && $oldtest==1) {
               $oldtest=0;
               $return .= "<br><p class='kiscim'>Újszövetség</p>";
            }
            $return .= "<a href='" . $baseurl . "index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='link'>" . $rs->fields["name"] . "</a><br>\n";
            $return .= $sb; /*
            list($res1, $res2, $res3, $res4)=listchapter($db, $reftrans, $rs->fields["abbrev"],"1");
            if ($res4->GetNumOfRows() > 0) {
                $res4->firstRow();
                if (strlen(trim($res4->fields["title"]))>0) {
                    $title = preg_replace("/<br>/",".",$res4->fields["title"]);
                    $title = preg_replace("/\.\./",". ",$title);
                    $return .= "<i>$title.</i> \n";
                }
                $verses=$res4->fields["verse"];
                for ($i=1;$i<5;$i++) {
                  $res4->nextrow();
                  $verses=$verses . " " . $res4->fields["verse"];
                }
                $return .= showintro($verses,200);
                $return .= "<a href='" . $baseurl . "index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='link'> >> </a><br>\n";
                $return .="$se<br>";
             } */
            $rs->nextRow();
	 } while (!$rs->EOF);
        #echo "</blockquote>";
		$return .="$se<br>";
        //$return .= showbookabbrevlist($db,$reftrans,"");
    }
	return $return;
}

function showbook($db, $reftrans, $abbook, $rs) {
    global $baseurl, $sb, $se, $title;
	$return = false;
    if ($rs->GetNumOfRows() > 0) {
        $title = showhier($db, $reftrans, $abbook, "");
        $output="";
        $rs->firstRow();
	 do {
            $return .= "<a href='" . $baseurl . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $rs->fields["numch"] ."' class='link'>" . $rs->fields["numch"] . ". fejezet</a><br>\n";
            $return .= $sb;
            list($res1, $res2, $res3, $res4)=listchapter($db, $reftrans, $abbook, $rs->fields["numch"]);
            if ($res4->GetNumOfRows() > 0) {
                $res4->firstRow();
                if (strlen(trim($res4->fields["title"]))>0) {
                    $title2 = preg_replace("/<br>/",".",$res4->fields["title"]);
                    $title2 = preg_replace("/\.\./",". ",$title2);
                    $return .= "<i>$title2.</i> \n";
                }
                $verses=$res4->fields["verse"];
                for ($i=1;$i<5;$i++) {
                  $res4->nextrow();
                  $verses=$verses . " " . $res4->fields["verse"];
                }
                $return .= showintro($verses,200);
                $return .= "<a href='" . $baseurl . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $rs->fields["numch"] ."' class='link'> >> </a><br>\n";
                $return .="$se<br>";
            }
            $rs->nextRow();
	 } while (!$rs->EOF);
    //$return .= showbookabbrevlist($db,$reftrans,$abbook);
    }
	return $return;
}


function showchapter($db, $reftrans, $abbook, $numch, $rs) {
    global $sb, $se, $title;
	$return = false;
    if ($rs->GetNumOfRows() > 0) {
        $title = showhier($db, $reftrans, $abbook, $numch);
        $return .=shownextprev($db, $reftrans, $abbook, $numch);
        //$return .= "<br><br>";
        $rs->firstRow();
	 do {
            if (strlen(trim($rs->fields["title"]))>0) {
               $return .= "<center><p class='kiscim'>" . $rs->fields["title"] . "</center></p>\n";
            }
            $return .= $sb;
            $return .= "&nbsp;<span class='kicsi'><sup>" . $rs->fields["numv"] . "</sup></span>";
            $return .= "<a name='" . $rs->fields["numv"] . "'></a>";
            
			$rs->fields["verse"] = preg_replace('/>>>/','>»',$rs->fields["verse"]);
			$rs->fields["verse"] = preg_replace('/>>/','»',$rs->fields["verse"]);
			$rs->fields["verse"] = preg_replace('/<<</','«<',$rs->fields["verse"]);
			$rs->fields["verse"] = preg_replace('/<</','«',$rs->fields["verse"]);
			
			$rs->fields["verse"] = preg_replace('/ "/',' „',$rs->fields["verse"]);
			$rs->fields["verse"] = preg_replace('/"( |,|\.|$)/','”$1',$rs->fields["verse"]);
			
			
			$return .= $rs->fields["verse"];
            $return .= $se;
            $rs->nextRow();
	 } while (!$rs->EOF);
        $return .= shownextprev($db, $reftrans, $abbook, $numch);
        $rs->firstRow();
        if ($reftrans==2) {
          $return .= "<p class='kiscim'>Hivatkozások</p>\n";
	  do {
            if (strlen(trim($rs->fields["refs"]))>0) {
               $return .= $sb;
               $return .= $rs->fields["numv"] . ": ";
               $return .= showverselinks($rs->fields["refs"],$reftrans) . "<br>";
               $return .= $se;
            }
            $rs->nextRow();
	 }while (!$rs->EOF);
        }

    }
	return $return;
}

function showbookabbrevlist($db,$reftrans,$abbook) {
	$return = false;
   $rs=$db->execute("select * from tdbook where reftrans =" . $reftrans . " order by bookorder");
   if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
        $return .= "<blockquote><p class='kicsi' align='center'>";
        $beginflag=0;
        do {
            if ($beginflag==0) {
               $beginflag=1;
            } else {
               $return .= " - ";
            }
            if ($rs->fields["abbrev"]==$abbook) {
                $return .= "<b>" . $rs->fields["abbrev"] . "</b>";
            } else {
                $return .= "<a href='index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='minilink'>" . $rs->fields["abbrev"] . "</a>";
            }
            $rs->nextRow();
         } while (!$rs->EOF);
       $return .= "</p></blockquote>";
   }
   return $return;
}

function showcomms($db, $rs, $reftrans, $rsmin, $rsmax) {
    global $sb, $se;
	$return = false;
	
    if ($rs->GetNumOfRows() > 0 && $reftrans==1) {
        //$return .= "<h4>Magyarázatok</h4>";
        $rs->firstRow();
	 do {
//	 print_R($rs->fields);
             $return .= "<p><span class='kiscim'>" . $rs->fields["bbook"] . " " . $rs->fields["bchap"] . "," . $rs->fields["bverse"];
             if (!($rs->fields["bbook"]==$rs->fields["ebook"] and $rs->fields["bchap"]==$rs->fields["echap"] and $rs->fields["bverse"]==$rs->fields["everse"])) {
                $return .= " - " . $rs->fields["ebook"] . " " . $rs->fields["echap"] . "," . $rs->fields["everse"];
             }
             $return .=  ":</span> ";
             if ($rs->fields["refbvers"]<$rsmin or $rs->fields["refevers"]>$rsmax) {
                $return .= showverselinks(showintro($rs->fields["comm"],200),$reftrans);
                $return .= "<a href='" . $baseurl . "index.php?q=showcomm&reftrans=" . $reftrans ."&did=". $rs->fields["did"] . "' class='link'> >> </a><br>\n";
                $return .="";
             } else {
               $return .= showverselinks($rs->fields["comm"],$reftrans);// . "<br><br>";
             }
             $return .= "</p>";
             $rs->nextRow();
	 } while (!$rs->EOF);
    }
	return $return;
}


function showcomm($db, $reftrans, $did) {
    global $sb, $se, $ptitle;
	$return = false;
	$query = "select * from tdcomm where did ='" . $did."';";
    $rs = $db->execute($query);
    if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
             $ptitle = "Kommentár: <a href='index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $rs->fields["bbook"] . "&numch=" .$rs->fields["bchap"] . "'>";
             $ptitle .=  $rs->fields["bbook"] . " " . $rs->fields["bchap"] . "," . $rs->fields["bverse"] . "</a>";
             if (!($rs->fields["bbook"]==$rs->fields["ebook"] and $rs->fields["bchap"]==$rs->fields["echap"] and $rs->fields["bverse"]==$rs->fields["everse"])) {
                $ptitle .= " - <a href='index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $rs->fields["ebook"] . "&numch=" .$rs->fields["echap"] . "'>";
                $ptitle .= $rs->fields["ebook"] . " " . $rs->fields["echap"] . "," . $rs->fields["everse"] . "</a>";
             }
			 global $q;
//			 if($q != 'showcomm') $return .= $ptitle;
             //$return .=  "<br>$sb";
             $return .= showverselinks($rs->fields["comm"],$reftrans) . "<br><br>";
             $return .= $se;
    }
	return $return;
}


function showintro ($text,$length) {
  $sentences = preg_split("/([\.\!\?,:;])+/",$text,-1,PREG_SPLIT_DELIM_CAPTURE);
  $finallength=strlen($text);
  $numbersentences=count($sentences)-1;
  while ($finallength>$length) {
     $finallength=$finallength-strlen($sentences[$numbersentences]);
     $numbersentences--;
  }
  if ($numbersentences < count($sentences)-1) {
    $text = "";
    for ($i = 0; $i <= $numbersentences; $i++) {
      $text = $text . $sentences[$i];
    }
    $text=$text . substr($sentences[$numbersentences+1],0,1);
  }
  return $text;
}

function showverselinks($text, $reftrans) {
  return preg_replace("/([0-9]*[a-zA-ZáÁéÉíÍóÓöÖőŐúÚüÜűŰ]+) ([0-9]+),([0-9]+)/","<a href=\"index.php?q=showchapter&reftrans=$reftrans&abbook=$1&numch=$2#$3\" class='link'>$1 $2,$3</a>",$text);
}


function showhier ($db, $reftrans, $abbook, $numch) {
    global $baseurl, $fileurl;
    $output = "<a href='" . $baseurl . "index.php?q=showbible' >Bibliák</a>\n";
	//$output = "<a href='" . $baseurl . "showbible.php' class='link'>Bibliák</a>\n";
    if (!empty($reftrans)) {
         $output = $output . " <img src='".$fileurl."img/arrowright.jpg'> ";
         $output = $output . "<a href='" . $baseurl . "index.php?q=showtrans&reftrans=" . $reftrans . "' >" . gettransname($db,$reftrans) . "</a>\n";
         if (!empty($abbook)) {
              $output = $output . " <img src='".$fileurl."img/arrowright.jpg'> ";
              $output = $output . "<a href='" . $baseurl . "index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $abbook . "' >" . getbookname($db,$reftrans,$abbook) . "</a>\n";
              if (!empty($numch)) {
                 $output = $output . " <img src='".$fileurl."img/arrowright.jpg'> ";
                 $output = $output . "<a href='" . $baseurl . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $numch . "' >" . $numch . ". fejezet</a>\n";
              }
        }
    }
    return $output;
}

function shownextprev ($db, $reftrans, $abbook, $numch) {
   $return = false;
   $return .= "<table width='100%'><tr>";
   if ($numch>1) {
      $return .= "<td align='left'>";
      $return .= "<a href='" . $baseurl . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch-1) ."' class='link'><< előző</a>";
      $return .= "</td>";
   }
   $querystring="select max(numch) as maxcount from tdverse where reftrans=" . $reftrans . " and abbook='" . $abbook . "'";
   $rs=$db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
     $maxcount=$rs->fields["maxcount"];
     if ($numch<$maxcount) {
          $return .= "<td align='right'>";
          $return .= "<a href='" . $baseurl . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch+1) ."' class='link'>következő >></a>";
          $return .= "</td>";
     }
   }
   $return .= "</tr></table>";
   return $return;
}

function advsearchbible($db, $texttosearch, $reftrans, $offset = 0, $rows = 50) {

      #$rs1 = $db->execute("select count(*) as cnt from tdverse where MATCH (verse) AGAINST ('" . $texttosearch . "') and reftrans=$reftrans");
      //$rs1 = $db->execute("select count(*) as cnt from tdverse where verse regexp '" . $texttosearch . "' and reftrans=$reftrans");
	  
	  $words = explode(' ',$texttosearch);
	  $where = ''; $query = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
		
		$query2 .= "title regexp '".$word."' ";
		if($k < (count($words)-1)) $query2 .= ' AND ';
	  }
	  $query = "select count(*) as cnt from tdverse where ((".$query.") OR (".$query2."))  and reftrans=$reftrans";
	  //echo $query;
	  $rs1 = $db->execute($query);
	  
      $rs1->firstRow();
      $resultcount = $rs1->fields["cnt"];
      $rs1->close();

      if ($resultcount > 0) {
       if (empty($rows)) {$rows = 50;}
       elseif ($rows>100) {$rows=100;}
       elseif ($rows<0) {$rows=50;}

       if (empty($offset)) {$offset = 0;}
       elseif ($offset>$resultcount) {$offset = (resultcount - ($resultcount % $rows));}
       elseif ($offset<0) {$offset = 0;}

       #$querystring = "select * from tdverse where MATCH (verse) AGAINST ('" . $texttosearch . "')>1 and reftrans=$reftrans order by did limit $offset, $rows";
       $querystring = "select * from tdverse where (verse regexp '" . $texttosearch . "' OR title regexp '" . $texttosearch . "') and reftrans=$reftrans order by did limit $offset, $rows";

		$words = explode(' ',$texttosearch);
		$query = ''; $query2 = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
		
		$query2 .= "title regexp '".$word."' ";
		if($k < (count($words)-1)) $query2 .= ' AND ';
	  }
	  $querystring = "select * from tdverse where ((".$query.") OR (".$query2."))  and reftrans=$reftrans order by did limit $offset, $rows";
	   
       //echo "<br>" . $querystring . "<br>\n";
       $rs = $db->execute($querystring);
	   //echo "<pre>".print_R($rs,1);
       return array($rs, $resultcount, $offset, $rows);
      }
}

function showverse($db,$reftrans,$abbook,$numch,$fromnumv,$tonumv) {
  $return = '';
  $rs = $db->execute("select * from tdverse where reftrans = $reftrans and abbook='". $abbook . "' and numch=$numch and numv>=". $fromnumv . " and numv <=" . $tonumv);
  if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
     $return .= "<p class='alap'><b>" . $rs->fields["abbook"] . " " . $rs->fields["numch"] . ",";
     if ($fromnumv==$tonumv) {
       $return .= $fromnumv . ":</b> \n";
     } else {
       $return .= $fromnumv . "-" . $tonumv . ":</b> \n";
     }
     do {
        $return .= "&nbsp;<span class='kicsi'><sup>" . $rs->fields["numv"] . "</sup></span>";
        $return .= $rs->fields["verse"] . "\n";
        $rs->nextRow();
     } while (!$rs->EOF);
     $return .= "</p>";
  } else {
     $return .= "Nincs ilyen bibliai vers. Valószínűleg hibásan adta meg a könyvet, a fejezetet vagy a verset.";
  }
  $rs->close();
  return $return;
}


function showverses($rs,$script,$reftrans) {
  global $sb, $se, $fileurl;
  $return = '';
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
            $return .= "<img src=".$fileurl."img/arrowright.jpg>&nbsp;";
			$return .= shln($rs->fields["abbook"] . " " . $rs->fields["numch"] . "," . $rs->fields["numv"],$script . "&reftrans=" . $rs->fields['reftrans'] . "&abbook=" . $rs->fields["abbook"] . "&numch=" . $rs->fields["numch"] . "#" . $rs->fields["numv"],"link") . "\n";
            $return .= " - $sb" . $rs->fields["verse"] . $se . "\n";
            $return .= "<br>\n";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
  return $return;
}


function showversesnextprev($request, $catcount, $offset, $rows, $paramchr){
	global $fileurl;
  $return =  "<table><tr>";

  if (!empty($request) && !empty($catcount)) {
     $request = preg_replace("/ /","_",$request);
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
         $return .= "<td align='left' width='50%'>";
		 $return .= "<img src='".$fileurl."img/arrowleft.jpg'> ";
         $return .= shln($prevstring , $request . $paramchr . "offset=$prevoffset&rows=$prevrows","link");
         $return .= "&nbsp;</td>";
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
         $return .= "<td align='right'  width='50%'>&nbsp;";
         $return .= shln($nextstring , $request .  $paramchr . "offset=$nextoffset&rows=$rows","link");
		 $return .= " <img src='".$fileurl."img/arrowright.jpg'>";
         $return .= "</td>";
     }
     $return .= "</tr></table>";
  }
  return $return;
}

function gettransname($db, $reftrans,$rov = false) {
 if($rov == false) return dlookup($db, "name","tdtrans","did=$reftrans");
 else return dlookup($db, "abbrev","tdtrans","did=$reftrans");
}

function getbookname($db, $reftrans, $abbook) {
 return dlookup($db, "name","tdbook","reftrans=$reftrans and abbrev='" . $abbook ."'");
}

function shln($name,$url,$class) {

  return "<a href='" . $url . "' class='" . $class . "'>" . $name . "</a>";

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

function displaytextfield ($name,$size,$maxlength,$value,$comment,$class){
  return "<span class='alap'>$comment</span><br><input type=text name='". $name ."' size=$size maxlength=$maxlength value='" . $value . "' class='" .$class."'>\n";
}


function displaytextarea ($name,$cols,$rows,$value,$comment,$class){
  return "<span class='alap'>$comment</span><br><textarea name='" .$name ."' cols=$cols rows=$rows wrap class='" .$class."'>" . $value . "</textarea><br>\n";
}

function displayoptionlist($name,$size,$rs,$valuefield,$listfield,$default,$comment,$class){
  $return = '';
  $return .= "<span class='alap'>$comment</span> <br>";
  $return .= "<select name='". $name . "' size='" . $size . "' class='" .$class."'>\n";
  if ($rs->GetNumOfRows() > 0) {
    $rs->firstRow();
    $i=1;
    do {
       $return .= "<option ";
          if (!empty($default)){
            if ($default == $rs->fields[$valuefield]) {$return .= "selected ";}
          }
          $return .= "value ='" . $rs->fields[$valuefield] . "' class='" .$class."'>" . $rs->fields[$listfield] . "</option>\n";
          $rs->nextRow();
       $i++;
    } while ((!$rs->EOF));
  }
  if(isset($rs->close)) $rs->close();
  $return .= "</select>\n";
  return $return;
}

/* 
 * Adatbázis kezelő függvények. Általában db.php néven...
 *
 *
 */
 
 function db_connect() {
	
	$user="root";
	$password="Felpecz";
	$database="bible";

	if($_SERVER['HTTP_HOST'] == 'localhost') $password = '';
	$db_link = mysql_connect('localhost:3306',$user,$password) or die ("Can't connect to mysql");
	//mysql_set_charset('utf8',$db_link);
	mysql_query("SET CHARACTER SET 'utf8'");
	//mysql_set_charset('utf-8');
	mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $db_link);
	
	if ($db_link) @mysql_select_db($database);
	return $db_link;
}

function db_close() {
	if(isset($db_link)) { $result = mysql_close($db_link);
	return $result;
	}
}

function db_query($query,$debug = '',$return = '') {
	
	if($debug == 'x' or $debug == '') $debug = 0;
	 /* 
	  * Debug 1 -> ha hiba van jelzi
	  * Debug 2 -> mindenképp közöl valamit
	  * FIGYELEM! a debug 2 kilövi az ajax_framewokr.js-t mert a mysqltoxml.php nem xml válasza miatt!
	  *
	  */
	  db_close();
	
	db_connect();
	
	if(!($result = mysql_query($query))) $error = mysql_errno().": ".mysql_error()." (<i>$query</i>)\n<br>";
	
	if($debug==1 and isset($error)) echo $error;
	elseif($debug==2 and !isset($error)) echo $query."<br>\n";
	elseif($debug==2) echo $error;

	//FIXIT: insert esetén nem megy a fetch, de akkor nincs error hadling;
	if(is_bool($result)) return;
	$rows = array();
	while(($row = mysql_fetch_array($result,  MYSQL_ASSOC))) {
	//echo"<br>--";print_R($row);
		foreach($row as $k => $i) {
			//$row[$k] = $i;
			$row[$k] = $i;
		}
		$rows[] = $row;
		
	}
	if($rows!=array()) return $rows;
	mysql_free_result($result);
	
	//echo "++".mysql_affected_rows()."++";
	/*
	 * Ezt itten kivételeztem.
	 */
	//if(!isset($error) AND isset($return)) return $return();
	db_close();
	if(isset($error)) return false;
	else return true;

	}
	

?>
