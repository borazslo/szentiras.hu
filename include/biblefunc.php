<?php

$sb="<span class='alap'>";
$se="</span>";

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
        $rs2 = $db->execute("select * from tdcomm where not (refbvers>" . $rsmax . " or refevers<" . $rsmin . ") and reftrans =" . $reftrans . " order by did");
        return array($rs2, $rsmin, $rsmax);
    }
}

function showbible($db, $rs) {
    global $baseurl;
    if ($rs->GetNumOfRows() > 0) {
        showhier($db, "", "", "");
        echo "<br><br><blockquote>";
        $rs->firstRow();
	 do {
            echo "<p><a href='" . $baseurl . "showtrans.php?reftrans=" . $rs->fields["did"] . "' class='link'>" . $rs->fields["name"] . " (" . $rs->fields["denom"] . ")</a></p>\n";
            $rs->nextRow();
	 } while (!$rs->EOF);
        echo "</blockquote>";
    }
}

function showtrans($db, $reftrans, $rs) {
    global $baseurl,$sb,$se;
    if ($rs->GetNumOfRows() > 0) {
        showhier($db, $reftrans, "", "");
        showbookabbrevlist($db,$reftrans,"");
        #echo "<blockquote>";
        echo "<p class='kiscim'>Ószövetség</p>";
        $oldtest=1;
        $rs->firstRow();
	 do {
            if ($rs->fields["oldtest"]==0 && $oldtest==1) {
               $oldtest=0;
               echo "<p class='kiscim'>Újszövetség</p>";
            }
            echo "<a href='" . $baseurl . "showbook.php?reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='link'>" . $rs->fields["name"] . "</a><br>\n";
            echo $sb;
            list($res1, $res2, $res3, $res4)=listchapter($db, $reftrans, $rs->fields["abbrev"],"1");
            if ($res4->GetNumOfRows() > 0) {
                $res4->firstRow();
                if (strlen(trim($res4->fields["title"]))>0) {
                    $title = preg_replace("/<br>/",".",$res4->fields["title"]);
                    $title = preg_replace("/\.\./",". ",$title);
                    echo "<i>$title.</i> \n";
                }
                $verses=$res4->fields["verse"];
                for ($i=1;$i<5;$i++) {
                  $res4->nextrow();
                  $verses=$verses . " " . $res4->fields["verse"];
                }
                echo showintro($verses,200);
                echo "<a href='" . $baseurl . "showbook.php?reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='link'> >> </a><br>\n";
                echo"$se<br>";
             }
            $rs->nextRow();
	 } while (!$rs->EOF);
        #echo "</blockquote>";
        showbookabbrevlist($db,$reftrans,"");
    }
}

function showbook($db, $reftrans, $abbook, $rs) {
    global $baseurl, $sb, $se;
    if ($rs->GetNumOfRows() > 0) {
        showhier($db, $reftrans, $abbook, "");
        echo "<br><br>";
        $output="";
        $rs->firstRow();
	 do {
            echo "<a href='" . $baseurl . "showchapter.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $rs->fields["numch"] ."' class='link'>" . $rs->fields["numch"] . ". fejezet</a><br>\n";
            echo $sb;
            list($res1, $res2, $res3, $res4)=listchapter($db, $reftrans, $abbook, $rs->fields["numch"]);
            if ($res4->GetNumOfRows() > 0) {
                $res4->firstRow();
                if (strlen(trim($res4->fields["title"]))>0) {
                    $title = preg_replace("/<br>/",".",$res4->fields["title"]);
                    $title = preg_replace("/\.\./",". ",$title);
                    echo "<i>$title.</i> \n";
                }
                $verses=$res4->fields["verse"];
                for ($i=1;$i<5;$i++) {
                  $res4->nextrow();
                  $verses=$verses . " " . $res4->fields["verse"];
                }
                echo showintro($verses,200);
                echo "<a href='" . $baseurl . "showchapter.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $rs->fields["numch"] ."' class='link'> >> </a><br>\n";
                echo"$se<br>";
            }
            $rs->nextRow();
	 } while (!$rs->EOF);
    showbookabbrevlist($db,$reftrans,$abbook);
    }
}


function showchapter($db, $reftrans, $abbook, $numch, $rs) {
    global $sb, $se;
    if ($rs->GetNumOfRows() > 0) {
        showhier($db, $reftrans, $abbook, $numch);
        shownextprev($db, $reftrans, $abbook, $numch);
        echo "<br><br>";
        $rs->firstRow();
	 do {
            if (strlen(trim($rs->fields["title"]))>0) {
               echo "<center><p class='kiscim'>" . $rs->fields["title"] . "</center></p>\n";
            }
            echo $sb;
            echo "&nbsp;<span class='kicsi'><sup>" . $rs->fields["numv"] . "</sup></span>";
            echo "<a name='" . $rs->fields["numv"] . "'></a>";
            echo $rs->fields["verse"];
            echo $se;
            $rs->nextRow();
	 } while (!$rs->EOF);
        shownextprev($db, $reftrans, $abbook, $numch);
        $rs->firstRow();
        if ($reftrans==2) {
          echo "<p class='kiscim'>Hivatkozások</p>\n";
	  do {
            if (strlen(trim($rs->fields["refs"]))>0) {
               echo $sb;
               echo $rs->fields["numv"] . ": ";
               echo showverselinks($rs->fields["refs"],$reftrans) . "<br>";
               echo $se;
            }
            $rs->nextRow();
	 }while (!$rs->EOF);
        }

    }
}

function showbookabbrevlist($db,$reftrans,$abbook) {
   $rs=$db->execute("select * from tdbook where reftrans =" . $reftrans . " order by bookorder");
   if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
        echo "<blockquote><p class='kicsi' align='center'>";
        $beginflag=0;
        do {
            if ($beginflag==0) {
               $beginflag=1;
            } else {
               echo " - ";
            }
            if ($rs->fields["abbrev"]==$abbook) {
                echo "<b>" . $rs->fields["abbrev"] . "</b>";
            } else {
                echo "<a href='showbook.php?reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbrev"] . "' class='minilink'>" . $rs->fields["abbrev"] . "</a>";
            }
            $rs->nextRow();
         } while (!$rs->EOF);
       echo "</p></blockquote>";
   }
}

function showcomms($db, $rs, $reftrans, $rsmin, $rsmax) {
    global $sb, $se;
    if ($rs->GetNumOfRows() > 0 && $reftrans==1) {
        echo "<p class='kiscim'>Magyarázatok</p>";
        $rs->firstRow();
	 do {
             echo "$sb<b>" . $rs->fields["bbook"] . " " . $rs->fields["bchap"] . "," . $rs->fields["bverse"];
             if (!($rs->fields["bbook"]==$rs->fields["ebook"] and $rs->fields["bchap"]==$rs->fields["echap"] and $rs->fields["bverse"]==$rs->fields["everse"])) {
                echo " - " . $rs->fields["ebook"] . " " . $rs->fields["echap"] . "," . $rs->fields["everse"];
             }
             echo  "</b><br>";
             if ($rs->fields["refbvers"]<$rsmin or $rs->fields["refevers"]>$rsmax) {
                echo showverselinks(showintro($rs->fields["comm"],200),$reftrans);
                echo "<a href='" . $baseurl . "showcomm.php?reftrans=" . $reftrans ."&did=". $rs->fields["did"] . "' class='link'> >> </a><br>\n";
                echo"<br><br>";
             } else {
               echo showverselinks($rs->fields["comm"],$reftrans) . "<br><br>";
             }
             echo $se;
             $rs->nextRow();
	 } while (!$rs->EOF);
    }
}


function showcomm($db, $reftrans, $did) {
    global $sb, $se;

    $rs=$db->execute("select * from tdcomm where did =" . $did);
    if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
             echo "<a href='showchapter.php?reftrans=" . $reftrans . "&abbook=" . $rs->fields["bbook"] . "&numch=" .$rs->fields["bchap"] . "' class='link'>";
             echo  $rs->fields["bbook"] . " " . $rs->fields["bchap"] . "," . $rs->fields["bverse"] . "</a>";
             if (!($rs->fields["bbook"]==$rs->fields["ebook"] and $rs->fields["bchap"]==$rs->fields["echap"] and $rs->fields["bverse"]==$rs->fields["everse"])) {
                echo " - <a href='showchapter.php?reftrans=" . $reftrans . "&abbook=" . $rs->fields["ebook"] . "&numch=" .$rs->fields["echap"] . "' class='link'>";
                echo $rs->fields["ebook"] . " " . $rs->fields["echap"] . "," . $rs->fields["everse"] . "</a>";
             }
             echo  "<br>$sb";
             echo showverselinks($rs->fields["comm"],$reftrans) . "<br><br>";
             echo $se;
    }
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
  return preg_replace("/([0-9]*[a-zA-ZáÁéÉíÍóÓöÖõÕúÚüÜûÛ]+) ([0-9]+),([0-9]+)/","<a href=\"showchapter.php?reftrans=$reftrans&abbook=$1&numch=$2#$3\" class='link'>$1 $2,$3</a>",$text);
}


function showhier ($db, $reftrans, $abbook, $numch) {
    global $baseurl;
    $output = "<a href='" . $baseurl . "showbible.php' class='link'>Bibliák</a>\n";
    if (!empty($reftrans)) {
         $output = $output . " <img src='/img/arrowright.jpg'> ";
         $output = $output . "<a href='" . $baseurl . "showtrans.php?reftrans=" . $reftrans . "' class='link'>" . gettransname($db,$reftrans) . "</a>\n";
         if (!empty($abbook)) {
              $output = $output . " <img src='/img/arrowright.jpg'> ";
              $output = $output . "<a href='" . $baseurl . "showbook.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "' class='link'>" . getbookname($db,$reftrans,$abbook) . "</a>\n";
              if (!empty($numch)) {
                 $output = $output . " <img src='/img/arrowright.jpg'> ";
                 $output = $output . "<a href='" . $baseurl . "showchapter.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $numch . "' class='link'>" . $numch . ". fejezet</a>\n";
              }
        }
    }
    echo $output;
}

function shownextprev ($db, $reftrans, $abbook, $numch) {
   echo "<br><br><table width='100%'><tr>";
   if ($numch>1) {
      echo "<td align='left'>";
      echo "<a href='" . $baseurl . "showchapter.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch-1) ."' class='link'><< elõzõ</a>";
      echo "</td>";
   }
   $querystring="select max(numch) as maxcount from tdverse where reftrans=" . $reftrans . " and abbook='" . $abbook . "'";
   $rs=$db->execute($querystring);
   if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
     $maxcount=$rs->fields["maxcount"];
     if ($numch<$maxcount) {
          echo "<td align='right'>";
          echo "<a href='" . $baseurl . "showchapter.php?reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch+1) ."' class='link'>következõ >></a>";
          echo "</td>";
     }
   }
   echo "</tr></table><br>";
}

function advsearchbible($db, $texttosearch, $reftrans, $offset = 0, $rows = 50) {

      #$rs1 = $db->execute("select count(*) as cnt from tdverse where MATCH (verse) AGAINST ('" . $texttosearch . "') and reftrans=$reftrans");
      //$rs1 = $db->execute("select count(*) as cnt from tdverse where verse regexp '" . $texttosearch . "' and reftrans=$reftrans");
	  
	  $words = explode(' ',$texttosearch);
	  $where = ''; $query = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
	  }
	  $rs1 = $db->execute("select count(*) as cnt from tdverse where ".$query." and reftrans=$reftrans");
	  
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
       $querystring = "select * from tdverse where verse regexp '" . $texttosearch . "' and reftrans=$reftrans order by did limit $offset, $rows";

		$words = explode(' ',$texttosearch);
		$query = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
	  }
	  $querystring = "select * from tdverse where ".$query." and reftrans=$reftrans order by did limit $offset, $rows";
	   
       #echo "<br>" . $querystring . "<br>\n";

       $rs = $db->execute($querystring);
       return array($rs, $resultcount, $offset, $rows);
      }
}

function showverse($db,$reftrans,$abbook,$numch,$fromnumv,$tonumv) {
  $rs = $db->execute("select * from tdverse where reftrans = $reftrans and abbook='". $abbook . "' and numch=$numch and numv>=". $fromnumv . " and numv <=" . $tonumv);
  if ($rs->GetNumOfRows() > 0) {
     $rs->firstRow();
     echo "<p class='alap'><b>" . $rs->fields["abbook"] . " " . $rs->fields["numch"] . ",";
     if ($fromnumv==$tonumv) {
       echo $fromnumv . ":</b> \n";
     } else {
       echo $fromnumv . "-" . $tonumv . ":</b> \n";
     }
     do {
        echo "&nbsp;<span class='kicsi'><sup>" . $rs->fields["numv"] . "</sup></span>";
        echo $rs->fields["verse"] . "\n";
        $rs->nextRow();
     } while (!$rs->EOF);
     echo "</p>";
  } else {
     echo "Nincs ilyen bibliai vers. Valószínûleg hibásan adta meg a könyvet, a fejezetet vagy a verset.";
  }
  $rs->close();
}


function showverses($rs,$script,$reftrans) {
  global $sb, $se;
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
            echo "<img src=/img/arrowright.jpg>&nbsp;";
	    echo shln($rs->fields["abbook"] . " " . $rs->fields["numch"] . "," . $rs->fields["numv"],$script . "?reftrans=" . $reftrans . "&abbook=" . $rs->fields["abbook"] . "&numch=" . $rs->fields["numch"] . "#" . $rs->fields["numv"],"link") . "\n";
            echo " - $sb" . $rs->fields["verse"] . $se . "\n";
            echo "<br>\n";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
}


function showversesnextprev($request, $catcount, $offset, $rows, $paramchr){

  echo "<br><br><table><tr>";

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

function gettransname($db, $reftrans) {
 return dlookup($db, "name","tdtrans","did=$reftrans");
}

function getbookname($db, $reftrans, $abbook) {
 return dlookup($db, "name","tdbook","reftrans=$reftrans and abbrev='" . $abbook ."'");
}

function shln($name,$url,$class) {

  echo "<a href='" . $url . "' class='" . $class . "'>" . $name . "</a>";

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
          if (!empty($default)){
            if ($default == $rs->fields[$valuefield]) {echo "selected ";}
          }
          echo "value ='" . $rs->fields[$valuefield] . "' class='" .$class."'>" . $rs->fields[$listfield] . "</option>\n";
          $rs->nextRow();
       $i++;
    } while ((!$rs->EOF));
  }
  if(isset($rs->close)) $rs->close();
  echo "</select><br>\n";
}

?>
