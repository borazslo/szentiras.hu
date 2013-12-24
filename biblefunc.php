<?php

$sb="<span class='alap'>";
$se="</span>";

$TMPtranslations = db_query('SELECT * FROM '.DBPREF.'tdtrans ORDER BY denom, name');
foreach($TMPtranslations as $TMP) {
	$translations[$TMP['id']] = $TMP;
	$GLOBALS['tdtrans_abbrev'][$TMP['id']] = $TMP['abbrev'];
	$GLOBALS['tdtrans'][$TMP['id']] = $TMP;
}
  
$books = db_query('SELECT * FROM '.DBPREF.'tdbook');
foreach($books as $TMP) {
	$bookabbrevs[$TMP['trans']][$TMP['abbrev']] = $TMP;
    $bookurls[$TMP['trans']][$TMP['url']] = $TMP;
	$GLOBALS['tdbook_url'][$TMP['trans']][$TMP['id']] = $TMP['url'];
	$GLOBALS['tdbook_abbrev'][$TMP['trans']][$TMP['id']] = $TMP['abbrev'];
	$GLOBALS['tdbook'][$TMP['trans']][$TMP['id']] = $TMP;
	
	if($TMP['oldtest'] == 1) $oldtest[$TMP['trans']][] = " ".DBPREF."tdverse.book = '".$TMP['id']."'";
	else $newtest[$TMP['trans']][] = " ".DBPREF."tdverse.book = '".$TMP['id']."'";
}


/* nem kell : */

function listbible($denom = false) {
    global $db;
    $query = "select * from ".DBPREF."tdtrans";
    if($denom != false) $query .= ' WHERE denom = "'.$denom.'" ';
    $query .= "    order by denom, name";
    
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
    
    return $rs;
}

function listtrans($reftrans) {
   global $db;
  $query = "select name, abbrev, oldtest FROM ".DBPREF."tdbook where trans = $reftrans order by id";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
  return $rs;
}

function listbook($reftrans, $abbook) {
    global $db;
  $query = "select chapter from ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".DBPREF."tdverse.trans = $reftrans and ".DBPREF."tdbook.abbrev='". $abbook . "' group by chapter order by chapter";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
  return $rs;
}

function listchapter($reftrans, $abbook, $numch, $max = 1000) {
   global $db;
  $query = "select gepi, ".DBPREF."tdverse.trans, did, numv, old, gepi, tip, verse FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON ".DBPREF."tdbook.id = ".DBPREF."tdverse.book  AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".DBPREF."tdverse.trans = $reftrans and abbrev='". $abbook . "' and chapter=$numch order by gepi, tip";
  $stmt = $db->prepare($query);
  //echo $query;
  $stmt->execute();
  $tmp = array();
  $rs = $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_GROUP);
  foreach($rs as $gepi => $verse) {
    foreach($verse as $key => $jelenseg) {
        $tmp[$gepi][$jelenseg->tip] = $jelenseg;
		if(count($tmp)>$max-1) return $tmp;
    }
  }
  return $tmp;
 }

function listcomm($rs,$reftrans) {
    global $db;
    if (count($rs) > 0) {
        
        $rsmin=array_shift($rs)->did;
        
        $rsmax=end($rs)->did;
		$query = "select * FROM ".DBPREF."tdcomm where not (refbvers>" . $rsmax . " or refevers<" . $rsmin . ") and reftrans =" . $reftrans . " order by did";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $rs2 = $stmt->fetchAll(PDO::FETCH_CLASS);
        return array($rs2,$rsmin,$rsmax);
    }
}

function showbible($rs,$type='') {
    global $db;
	global $title;
	
	$return = '';
    if (count($rs) > 0) {
        //$title = showhier($db, "", "", "");
        $return .= "<blockquote>";
        foreach($rs as $row) {
			if($type == 'simple') {
				$return .= "<a href='" . BASE . "index.php?q=showtrans&reftrans=" . $row->id . "' >" . $row->name . " (" . $row->denom . ")</a><br/>\n";
			} else {
				$return .= "<p><span class='alcim'><a href='" . BASE . "index.php?q=showtrans&reftrans=" . $row->id . "' class='alcim'>" . $row->name . " (" . $row->denom . ")</a></span>\n";
				$return .= "<br><span class='catlinksmall'>".$row->copyright."</span></p>";
			}
        } 
        $return .= "</blockquote>";
    }
	return $return;
}

function showtrans($reftrans, $rs) {
    global $sb,$se,$title;
    $return = false;
    if (count($rs) > 0) {
		
        $title = showhier($reftrans, "", "");
        $return .= showbookabbrevlist($reftrans,"");
        #echo "<blockquote>";
        $return .= "<p class='kiscim'>Ószövetség</p>";
        $oldtest=1;
    
	 foreach($rs as $row) {
            if ($row->oldtest==0 && $oldtest==1) {
               $oldtest=0;
               $return .= "<br><p class='kiscim'>Újszövetség</p>";
            }
            $return .= "<a href='" . BASE . "index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $row->abbrev . "' class='link'>" . $row->name . "</a><br>\n";
            $return .= $sb; 
	 } 
        #echo "</blockquote>";
		$return .="$se<br>";
        //$return .= showbookabbrevlist($db,$reftrans,"");
    }
	return $return;
}

function showbook($reftrans, $abbook, $rs) {
    global $sb, $se, $title;
	$return = false;
    if (count($rs) > 0) {
        $title = showhier($reftrans, $abbook, "");
        $output="";
        
	 foreach($rs as $row) {
            $return .= "<a href='" . BASE . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $row->chapter ."' class='link'>" . $row->chapter . ". fejezet</a><br>\n";
            $return .= $sb;
            
            $verses = listchapter($reftrans, $abbook, $row->chapter,2);
			
            if (count($verses) > 0) {
                
            
                      
                $i = 0; $verses2 = '';
                foreach($verses as $verse) {
                   $i++;
                  $verses2=$verses2 . " " . showverse($verse,$reftrans,'verseonly');
                  if($i>3) break;
                } 
                $return .= showintro($verses2,250);
                $return .= "<a href='" . BASE . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $row->chapter ."' class='link'> >> </a><br>\n";
                $return .="$se<br>";
            } 

	 } 
    //$return .= showbookabbrevlist($db,$reftrans,$abbook);
    }
	return $return;
}

function showchapter($reftrans, $abbook, $numch, $rs) {
    global $sb, $se, $title, $db;
	$return = false;
    if (count($rs) > 0) {
        $title = showhier($reftrans, $abbook, $numch);
        $return .=shownextprev($reftrans, $abbook, $numch);
        //$return .= "<br><br>";
    
    
    //if($reftrans == 1) { global $scripts;   $scripts[] = '2columns.js';   }
    $return .= '<div id="c2" class="chapter c2"></div><div id="data" class="chapter c1"><p>';
	 foreach($rs as $row) {
            $tmp = array_shift(array_values($row));
            if(isset($tmp->old) AND $tmp->old != 0) $oldmax = $tmp->old;
            if(isset($tmp->old) AND !isset($oldmin) AND $tmp->old != 0) $oldmin = $tmp->old;            
            
            $return .= showverse($row);    
	 } 
	 $return .= '</p></div>';
        $return .= shownextprev( $reftrans, $abbook, $numch);
		
        /*
		if(isset($oldmin)) { 
            global $translations;
            $return .= '<font size="-2">';
            $return .= $translations[$reftrans]['reference'].", ".$oldmin;
            if($oldmax > $oldmin) $return .= '-'.$oldmax;
            $return .= '. oldal</font><br/><br/>'; }
		*/
		$query = "SELECT id FROM ".DBPREF."tdbook WHERE abbrev = '".$abbook."' AND trans =  ".$reftrans;
	$results = db_query($query);
	if(count($results)>0) { 
	$bookid = $results[0]['id'];
	//$abbook." ".$numch." (".gettransname($reftrans,'true').")	
	$query = "SELECT * FROM ".DBPREF."tdbook WHERE id = '".$bookid."'";
	$results = db_query($query);
	if(!isset($content)) $content = '';
	if(count($results)>1) {
		foreach($results as $result) {
			//$transcode = preg_replace('/ /','',preg_replace("/^".$abbook."/",$result['abbrev'],$code['code']));
			$url = BASE.gettransname($result['trans'],'true')."/".$result['abbrev'].$numch;
			
			if($reftrans == $result['trans']) $style = " style=\"background-color:#9DA7D8;color:white;\" "; else $style = '';
			$change = "<a href=\"".$url."\" ".$style." class=\"button minilink\">".gettransname($result['trans'],'true')."</a> \n";
			$content .= $change;//echo $url;
		} 
		$content .= '<br>';
	} }
	$return .= $content;
		
    } 
	return $return;
}

function showbookabbrevlist($reftrans,$abbook) {
	global $db;
	$return = false;
    $query = "select * from ".DBPREF."tdbook where trans =" . $reftrans . " order by id";
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
    
   if (count($rs) > 0) {
        $return .= "<blockquote><p class='kicsi' align='center'>";
        $beginflag=0;
        foreach ($rs as $row) {
            if ($beginflag==0) {
               $beginflag=1;
            } else {
               $return .= " - ";
            }
            if ($row->abbrev==$abbook) {
                $return .= "<b>" . $row->abbrev . "</b>";
            } else {
                $return .= "<a href='". BASE."index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $row->abbrev . "' class='minilink'>" . $row->abbrev . "</a>";
            }
        } 
       $return .= "</p></blockquote>";
   }
   return $return;
}

function showverse($verse,$trans = false,$type = false) {    
    
    $return = '';
    if($type == 'verseonly') $verseonly = true;
    
    if(!is_array($verse)) {
        global $db;
        $query = "SELECT gepi, ".DBPREF."tdverse.trans, did, numv, gepi, tip, verse FROM ".DBPREF."tdverse WHERE gepi = ".$verse." AND trans = ".$trans;
        $stmt = $db->prepare($query);
        $stmt->execute();
        $rs = $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_GROUP);
        $rs =  array_shift(array_values($rs));
        $tmp = array();
        foreach($rs as $key => $jelenseg) {
                $tmp[$jelenseg->tip] = $jelenseg;
            }        
        $verse = $tmp;
    }
    if(count($verse)>0 AND $trans == false) { $trans = array_shift(array_values($verse))->trans; }   

    if($trans == 4) {
        /* tip: 6,13 */
        if(isset($verse[13])) {
            $hivatkozas = "<sup>(".preg_replace('/• /i','',$verse[13]->verse).")</sup>";
            $verse[6]->verse = preg_replace('/•/i',$hivatkozas,$verse[6]->verse);
        }
        $return .= "<sup><a id=\"".$verse[6]->numv."\"></a>".$verse[6]->numv."</sup>".$verse[6]->verse." ";
   } elseif($trans == 2) {
        /* tip: 6,7,(8,9) */
        if(isset($verse[7])) {            
            $sup = "<a title=\"Javasolt szöveg: ".strip_tags($verse[7]->verse)."\" id=\"".$verse[6]->numv."\">".$verse[6]->numv."</a>";
        } else $sup = "<a id=\"".$verse[6]->numv."\"></a>".$verse[6]->numv;
        $return .= "<sup>".$sup."</sup>".$verse[6]->verse." ";
    } elseif($trans == 1) {
        /* tip 401, 501, 601, 701, 704, 801, 901, 902, 904 */
        if(isset($verse[401]) AND !isset($verseonly)) $return .= '</p><p class="kiscim konyvcim">'.$verse[401]->verse.'</p><p>';
        if(isset($verse[501]) AND !isset($verseonly)) $return .= '</p><p class="kiscim cimsor1">'.$verse[501]->verse.'</p><p>';        
        if(isset($verse[601]) AND !isset($verseonly)) $return .= '</p><p class="kiscim cimsor2">'.$verse[601]->verse.'</p><p>';        
        if(isset($verse[701]) AND !isset($verseonly)) $return .= '</p><p><span class="kiscim cimsor3">'.$verse[701]->verse.'</span>';        
        if(isset($verse[704]) AND !isset($verseonly)) $return .= '</p><p><span class="kiscim cimsor4">'.$verse[704]->verse.'</span>';        
        
        if(isset($verse[801])) $numv = $verse[801]->verse;
        else $numv = numv($verse[901]->gepi);
         
        //if(isset($verse[902])) $return .= '<i>('.preg_replace('/\(.*?\)/i','',$verse[902]->verse).')</i>';        
        
        if(isset($verse[904])) $return .= "<sup><a title=\"Hiányzó vers\" id=\"".numv($verse[904]->gepi)."\">(".numv($verse[904]->gepi).")</a></sup>";         
        
        if(isset($verse[901])) $return .= "<sup><a id=\"".$numv."\"></a>".$numv."</sup>".$verse[901]->verse." ";
    } elseif($trans == 3) {
        /* tip 5,10,20,30,(35),(40),50,60,70,(80,90,95),110 */
        if(isset($verse[5]) AND !isset($verseonly)) $return .= '<p class="kiscim konyvcim">'.$verse[5]->verse.'</p>';
        if(isset($verse[10]) AND !isset($verseonly)) $return .= '<p class="kiscim cimsor1">'.$verse[10]->verse.'</p>';        
        if(isset($verse[20]) AND !isset($verseonly)) $return .= '<p class="kiscim cimsor2">'.$verse[20]->verse.'</p>';        
        if(isset($verse[30]) AND !isset($verseonly)) $return .= '<p class="kiscim cimsor3">'.$verse[30]->verse.'</p>';        
        
        if(isset($verse[70])) {            
            $sup = "<a title=\"Javasolt szöveg: ".strip_tags($verse[70]->verse)."\" id=\"".$verse[60]->numv."\">".$verse[60]->numv."</a>";
        } else $sup = "<a id=\"".$verse[60]->numv."\"></a>".$verse[60]->numv;
           
        if(isset($verse[110])) $return .= "<sup><a title=\"Hiányzó vers\" id=\"".$verse[110]->numv."\">(".$verse[110]->numv.")</a></sup>";         
        
        /* zsoltároknál ideiglenese kézzel tesszük be */        
        if(preg_match('/^121([0-9]{8})/',$verse[60]->gepi)) $return .= "<br/>";
        
        $return .= "<sup>".$sup."</sup>";
        if(isset($verse[50])) $return .= '<span class="heber">'.$verse[50]->verse.'</span><br/>';        
        $return .= $verse[60]->verse." ";
    
    }
    else {
    foreach($verse as $jel) {
                $return .= "<sup>".$jel->tip."</sup>".$jel->verse." ";
            
    /*                
			$row->verse = preg_replace('/>>>/','>»',$row->verse);
			$row->verse = preg_replace('/>>/','»',$row->verse);
			$row->verse = preg_replace('/<<</','«<',$row->verse);
			$row->verse = preg_replace('/<</','«',$row->verse);
			
			$row->verse = preg_replace('/ "/',' „',$row->verse);
			$row->verse = preg_replace('/"( |,|\.|$)/','”$1',$row->verse);
    */
    } 
    //$return .= "<pre>".print_r($verse,1)."</pre>";
    }
    if(!isset($verseonly)) $return = preg_replace('/\\\n/','</p><p>',$return);
    else $return = preg_replace('/\\\n/',' ',$return);
    
    return $return;
}

function showcomms($rs, $reftrans, $rsmin, $rsmax) {
    global $sb, $se;
	$return = false;
	
    if (count($rs) > 0 && $reftrans==1) {
        //$return .= "<h4>Magyarázatok</h4>";
        
	 foreach($rs as $row) {

             $return .= "<p><span class='kiscim'>" . $row->bbook . " " . $row->bchap . "," . $row->bverse;
             if (!($row->bbook==$row->ebook and $row->bchap==$row->echap and $row->bverse==$row->everse)) {
                $return .= " - " . $row->ebook . " " . $row->echap . "," . $row->everse;
             }
             $return .=  ":</span> ";
             if ($row->refbvers<$rsmin or $row->refevers>$rsmax) {
                $return .= showverselinks(showintro($row->comm,200),$reftrans);
                $return .= "<a href='" . BASE . "index.php?q=showcomm&reftrans=" . $reftrans ."&did=". $row->did . "' class='link'> >> </a><br>\n";
                $return .="";
             } else {
               $return .= showverselinks($row->comm,$reftrans);// . "<br><br>";
             }
             $return .= "</p>";
             
	 } 
    }
	return $return;
}

function showcomm($db, $reftrans, $did) {
    global $sb, $se, $ptitle;
	$return = false;
	$query = "select * FROM ".DBPREF."tdcomm where did ='" . $did."';";
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

  return preg_replace("/([0-9]*[a-zA-ZáÁéÉíÍóÓöÖőŐúÚüÜűŰ]+) ([0-9]+),([0-9]+)/","<a href=\"".BASE."index.php?q=showchapter&reftrans=$reftrans&abbook=$1&numch=$2#$3\" class='link'>$1 $2,$3</a>",$text);
}

function showhier ($reftrans, $abbook, $numch) { 
    $output = ""; // "<a href='" . BASE . "index.php?q=showbible' >Bibliák</a>\n";
	//$output = "<a href='" . BASE . "showbible.php' class='link'>Bibliák</a>\n";
    if (!empty($reftrans)) {
         //$output = $output . " <img src='".BASE."img/arrowright.jpg'> ";
         $output = $output . "<a href='" . BASE . "index.php?q=showtrans&reftrans=" . $reftrans . "' > " . gettransname($reftrans) . "</a>\n";
         if (!empty($abbook)) {
              $output = $output . " <img src='".BASE."img/arrowright.jpg'> ";
              $output = $output . "<a href='" . BASE . "index.php?q=showbook&reftrans=" . $reftrans . "&abbook=" . $abbook . "' >" . getbookname($abbook,$reftrans) . "</a>\n";
              if (!empty($numch)) {
                 $output = $output . " <img src='".BASE."img/arrowright.jpg'> ";
                 $output = $output . "<a href='" . BASE . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . $numch . "' >" . $numch . ". fejezet</a>\n";
              }
        }
    }
    return $output;
}

function shownextprev ($reftrans, $abbook, $numch) {	
    global $db;
	
   $return = false;
   $return .= "<table width='100%'><tr>";
   if ($numch>1) {
      $return .= "<td align='left'>";
      $return .= "<a href='" . BASE . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch-1) ."' class='link'><< előző</a>";
      $return .= "</td>";
   }
   $querystring="select max(chapter) as maxcount from ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans  WHERE ".DBPREF."tdbook.trans=" . $reftrans . " and ".DBPREF."tdbook.abbrev='" . $abbook . "'";
   $stmt = $db->prepare($querystring);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);

   if (count($rs) > 0) {
     
     $maxcount=$rs[0]->maxcount;
     if ($numch<$maxcount) {
          $return .= "<td align='right'>";
          $return .= "<a href='" . BASE . "index.php?q=showchapter&reftrans=" . $reftrans . "&abbook=" . $abbook . "&numch=" . ($numch+1) ."' class='link'>következő >></a>";
          $return .= "</td>";
     }
   }
   $return .= "</tr></table>";
   return $return;
}

function advsearchbible($db, $texttosearch, $reftrans, $offset = 0, $rows = 50) {
        return array(false, 0, 0, 0);
        /* IDEIGLENES TODO OUT*/
        
      #$rs1 = $db->execute("select count(*) as cnt from tdverse where MATCH (verse) AGAINST ('" . $texttosearch . "') and reftrans=$reftrans");
      //$rs1 = $db->execute("select count(*) as cnt from tdverse where verse regexp '" . $texttosearch . "' and reftrans=$reftrans");
	  
	  $rs = $db->execute("SELECT oldtest, trans, abbrev, id FROM ".DBPREF."tdbook WHERE trans = $reftrans ORDER BY trans ");
	  do {
		if($rs->fields['abbrev'] != '') {
			$books[$rs->fields['trans']][] = preg_replace('/ /','',$rs->fields['abbrev']);
			$abbrevs[preg_replace('/ /','',$rs->fields['abbrev'])] = preg_replace('/ /','',$rs->fields['abbrev']);
			$pattern = '/^'.preg_replace('/ /','',$rs->fields['abbrev']).'([0-9]{1,2}|$)/i';
			if($rs->fields['oldtest'] == 1) $oldtest[] = " ".DBPREF."tdverse.book = '".$rs->fields['id']."'";
			else $newtest[] = " ".DBPREF."tdverse.book = '".$rs->fields['id']."'";
		}
        $rs->nextRow();
	 } while (!$rs->EOF);
	  
	  //print_R($newtest);
	  
	   $pattern = "/in:(".implode("|",$abbrevs).")$/i";
	   $patternNew = "/in:(Újszöv|Új|Újszövetség|Ótestamentum|Ótestámentum)$/i";
	   $patternOld = "/in:(Ó|Ószöv|Ószövetség|Újtestamentum|Újtestámentum)$/i";
	   if(preg_match($pattern,$texttosearch,$matches)) {
			$texttosearch = trim(preg_replace($pattern,'',$texttosearch));
			$isinbook = " AND ".DBPREF."tdbook.abbrev = '".$matches[1]."' ";
	   } elseif(preg_match($patternOld,$texttosearch,$matches)) {
			$texttosearch = trim(preg_replace($patternOld,'',$texttosearch));
			$isinbook = " AND (".implode(' OR ',$oldtest).")";
	   } elseif(preg_match($patternNew,$texttosearch,$matches)) {
			$texttosearch = trim(preg_replace($patternNew,'',$texttosearch));
			$isinbook = " AND (".implode(' OR ',$newtest).")";
	   } else $isinbook = '';
	  
	  
	  $words = explode(' ',$texttosearch);
	  $where = ''; $query = ''; $query2 = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
		
		$query2 .= "title regexp '".$word."' ";
		if($k < (count($words)-1)) $query2 .= ' AND ';
	  }
	  $query = "select count(*) as cnt from ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans AND ".DBPREF."tdbook.id = ".DBPREF."tdverse.book where ((".$query.") OR (".$query2.")) ".$isinbook." and ".DBPREF."tdverse.trans=$reftrans";
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
       $querystring = "select * FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans AND ".DBPREF."tdbook.id = ".DBPREF."tdverse.book  where (verse regexp '" . $texttosearch . "' OR title regexp '" . $texttosearch . "') and ".DBPREF."tdverse.trans=$reftrans order by gepi limit $offset, $rows";

		$words = explode(' ',$texttosearch);
		$query = ''; $query2 = '';
	  foreach($words as $k=>$word) {
		$query .= "verse regexp '".$word."' ";
		if($k < (count($words)-1)) $query .= ' AND ';
		
		$query2 .= "title regexp '".$word."' ";
		if($k < (count($words)-1)) $query2 .= ' AND ';
	  }
	  $querystring = "select * from ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans AND ".DBPREF."tdbook.id = ".DBPREF."tdverse.book where ((".$query.") OR (".$query2.")) ".$isinbook." and ".DBPREF."tdverse.trans=$reftrans order by gepi limit $offset, $rows";
	   
       //echo "<br>" . $querystring . "<br>\n";
       $rs = $db->execute($querystring);
	   //echo "<pre>".print_R($rs,1);
       return array($rs, $resultcount, $offset, $rows);
      }
}

function showverses($rs,$script,$reftrans) {
  global $sb, $se, $books,$translations;

  $return = '';
  if ($rs->GetNumOfRows() > 0) {
         $rs->firstRow();
	 do {
			if($reftrans == 3) $rs->fields["verse"] = preg_replace_callback("/{(.*)}/",'replace_hivatkozas',$rs->fields["verse"]);
			//print_R($rs->fields);
            $return .= "<img src=".BASE."img/arrowright.jpg>&nbsp;";
			//$return .= shln($rs->fields["abbrev"] . " " . $rs->fields["chapter"] . "," . $rs->fields["numv"],BASE.$script . "&reftrans=" . $rs->fields['trans'] . "&abbook=" . $rs->fields["abbrev"] . "&numch=" . $rs->fields["chapter"] . "#" . $rs->fields["numv"],"link") . "\n";
			$return .= shln($rs->fields["abbrev"] . " " . $rs->fields["chapter"] . "," . $rs->fields["numv"],BASE.$translations[$rs->fields['trans']]['abbrev'] .'/'.$rs->fields["abbrev"] . $rs->fields["chapter"] . "#" . $rs->fields["numv"],"link") . "\n";
            $return .= " - $sb" . $rs->fields["verse"] . $se . "\n";
            $return .= "<br>\n";
	    $rs->nextRow();
	 } while (!$rs->EOF);
  }
  $rs->close();
  return $return;
}


function showversesnextprev($request, $catcount, $page, $rows, $paramchr){
	
	$return = '';
	//echo $request."-".$catcount."-".$page."-".$rows."-".$paramchr."<br>";
	
  $return =  "<table><tr>";

  if (!empty($request) && !empty($catcount)) {
     //$request = preg_replace("/ /","_",$request);
     if (empty($page)) {$page=1;}
     if (empty($rows)) {$rows=50;}
	 
	 $return .= "<td align='left' width='180px'>";
     if ($page > 1 AND $catcount > ($page - 1 ) * $rows ) {             
         $prevstring = ( ($page - 2) * $rows ) + 1 ." - ";
		 if( ($page - 1 )* $rows < $catcount) $prevstring .= ( ($page - 1 )* $rows );		 
		 else $prevstring .= $catcount;
		 $return .= "<img src='".BASE."img/arrowleft.jpg'> ";
		 $path = BASE.$request;
		 if( ($page - 1 ) > 1) $path .= $paramchr . "page=". ( $page - 1 );
		 if($rows != $GLOBALS['rows'])  $path .= "&rows=$rows";
         $return .= shln($prevstring , $path,"link");
     }
	 $return .= "&nbsp;</td>";
	 
	 $signs = ceil ($catcount / $rows);
	 if($signs > 1) {
	 $return .= "<td align='center' width='*'><span class='pager'>";
	 for($i=1;$i<=$signs;$i++) {
		$off = $i;
		$prevrows = $rows;
		if($off == $page) $return .= '<span style="background-color:#CFD4EC;padding:2px"><strong>';
		$path = BASE.$request;
		$path .= $paramchr . "page=". $off;
		if($rows != $GLOBALS['rows'])  $path .= "&rows=$rows";
		$return .= shln($i , $path,"link");
		
		if($off == $page) $return .= '</strong></span>';
		if($i < $signs) $return .= ' ';
	}
	$return .= '</span></td>';
	}
	 
	 $return .= "<td align='right'  width='180px'>&nbsp;";
     if ($catcount > ($page )* $rows ) {
		 $nextstring = ( ($page ) * $rows ) + 1 ." - " ;
		 if($catcount < ($page + 1)* $rows ) $nextstring .= 'talán '; //$catcount;
		 $nextstring .=  (($page + 1)* $rows );		 		
		 $return .= "<img src='".BASE."img/arrowright.jpg'> ";
		 $path = BASE.$request;
		 $path .= $paramchr . "page=". ( $page + 1 );
		 if($rows != $GLOBALS['rows'])  $path .= "&rows=$rows";
         $return .= shln($nextstring , $path,"link");
     }
	 $return .= "</td>";
     $return .= "</tr></table>";
  }
  return $return;
}

function gettransname($reftrans,$rov = false) {    
 if($rov == false) return dlookup("name","tdtrans","id=$reftrans");
 else return dlookup("abbrev","tdtrans","id=$reftrans");
}

function getbookname($abbook,$reftrans) {
 return dlookup("name","tdbook","trans=$reftrans and abbrev='" .$abbook ."'");
}

function shln($name,$url,$class) {

  return "<a href='" . $url . "' class='" . $class . "'>" . $name . "</a>";

}

function dlookup($field,$table,$condition) {
    global $db;
   $querystring= "select $field from ".DBPREF."$table where $condition";
   $stmt = $db->prepare($querystring);
   $stmt->execute();
   $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
   $return = array();
   foreach($rs as $key => $item) {
        $return[$key] = $item->$field;
   }
   if(count($return) == 1) return $return[0]; //array_shift(array_values($return));
   return $return;
   
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
  $return .= "<select name='". $name . "' id='". $name . "' onchange='change_".$name."()' size='" . $size . "' class='" .$class."'>\n";
  if (count($rs) > 0) {
    
    $i=1;
    foreach($rs as $row) {
       $return .= "<option ";
          if (!empty($default)){
            if ($default == $row->$valuefield) {$return .= "selected ";}
          }
          $return .= "value ='" . $row->$valuefield . "' class='" .$class."'>" . $row->$listfield . "</option>\n";
          
       $i++;
    } 
  }
  //if(isset($rs->close)) $rs->close();
  $return .= "</select>\n";
  return $return;
}

/* 
 * Adatbázis kezelő függvények. Általában db.php néven...
 *
 *
 */
 
 function db_connect() {
	
	$user="szentiras";
	$password="saritnezs11";
	$database="bible";

	//if($_SERVER['HTTP_HOST'] == 'localhost') $password = '';
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

function numv($gepi) {
    $abc = array('','a','b','c','d','e','f','g','h','i','j','k','l');
    
    return ((int) substr($gepi,7,2)).$abc[(int) substr($gepi,9,2)];


}    
function igenaptar($datum = false) {
	
	$return = '';
	if($datum == false) $datum = date('Y-m-d');

	$fn2 = "http://katolikus.hu/igenaptar/".date('Ymd',strtotime($datum)).".html";

	//$file = iconv("UTF-8", "ISO-8859-2",file_get_contents($fn2));
	$file = file_get_contents($fn2);

	preg_match('/<!-- helyek:(.*)-->/',$file,$tmp);
	if(isset($tmp[1])) $olvasmany_rov = trim($tmp[1]); else $olvasmany_rov = '';
	$olvasmanyok_rov = explode(';',$tmp[1]);
	preg_match('/<hr>([^x]*)<\/body>/',$file,$tmp);
	if(isset($tmp[1])) $olvasmany = trim($tmp[1]); else $olvasmany = '';
	
	$olvasmanyok = explode(';',$olvasmany_rov);
	$return .= '<p class="alcim">';
	foreach($olvasmanyok as $key => $olvasmany) {
		if(preg_match('/Zs ([0-9]{1,3})/',$olvasmany,$matches)) {
			$olvasmany = 'Zsolt'.(( (int) $matches[1]) + 1 );
		}
		$code = isquotetion(preg_replace('/ /','',$olvasmany));
		//echo"<pre>valami: ".$olvasmany; print_r($code); echo "<br><br>"; 
		if(is_array($code)) {
			$olvasmany = $code['code'];
			$return .= " <div style='height:20px'><a href='".BASE."KNB/".preg_replace('/ /','',preg_replace('/^'.$code['book'].'/',$code['bookurl'],$olvasmany))."' class='link'>".$olvasmany."</a> ";
	
		$query = "SELECT gepi FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans  WHERE ".DBPREF."tdverse.trans = ".$code['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$code['book']."' LIMIT 1";
		$result = db_query($query);
		if($result[0]['gepi']!='') {
			$query = "SELECT ".DBPREF."tdtrans.*, ".DBPREF."tdtrans.abbrev as transabbrev,".DBPREF."tdbook.abbrev,".DBPREF."tdbook.url, ".DBPREF."tdverse.trans FROM ".DBPREF."tdverse 
				LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans 
				LEFT JOIN ".DBPREF."tdtrans ON ".DBPREF."tdverse.trans = ".DBPREF."tdtrans.id WHERE gepi = ".$result[0]['gepi']."
				AND (tip = 60 OR tip=6 OR tip=901)
				 ORDER BY ".DBPREF."tdtrans.denom, ".DBPREF."tdtrans.name";
			$results = db_query($query);
		
			if(count($results)>1) {
		
			$content = "</div><div style='float:right;margin-top:-30px'>";
			foreach($results as $result) {
				
				$transcode = preg_replace('/ /','',preg_replace("/^".$code['book']."/",$code['bookurl'],$code['code']));
				$url = BASE.$result['transabbrev']."/".$transcode;
				
				//if($transcode = $code['code'] AND $code['reftrans'] == $['trans']) $style = " style=\"background-color:#9DA7D8;color:white;\" "; else $style = '';
				$style = '';
				$change = "<a href=\"".$url."\" ".$style." class=\"button minilink\">".$result['transabbrev']."</a>\n";
				$content .= $change;//echo $url;
			} }
			
			if($key > count($olvasmanyok) -2) $content .= "<a href=\"http://evangelium.katolikus.hu/audio/NE".date('Ymd').".mp3\" class=\"button minilink\" title=\"Evangélium és elmélkedés az evangelium365.hu honlapról.\" target=\"_blank\">mp3</a>";
			
			
			$return .=  $content."</div><br>";
		
			}
		}
		
	
	
	}
	$return .= '</p>';

	return $return;

}	

?>
