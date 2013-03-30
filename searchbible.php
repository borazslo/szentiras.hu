<?php

  $min = 20;
  $max = 100;
  if (isset($_REQUEST['offset'])) $offset = $_REQUEST['offset']; else $offset = 0;
  if (isset($_REQUEST['rows'])) $rows = $_REQUEST['rows']; else $rows = 50;
  

if($texttosearch == '') { 
		$content .= printSearchForm(); 
} else {
 
  require("JSON.php"); /* PHP 5.2 >= esetén */
  $texttosearch = trim($texttosearch);
  $texttosearch = preg_replace("/(_)/"," ",$texttosearch);
  $texttosearch = preg_replace("/([\.,;:_]*)$/","",$texttosearch);
  
  // TODO: mtÖrv -> MTörv!!
  preg_match("/^[0-9]{0,1}([^\d]{1,20})[0-9]{1,2}/",$texttosearch,$matches);
  if(isset($matches[1])) { 
	$texttosearch = preg_replace("/^([0-9]{0,1})([^\d]{1,20})([0-9]{1,2})/","$1".ucfirst(strtolower($matches[1]))."$3",$texttosearch);
	$texttosearch = preg_replace("/ /","",$texttosearch);
  }
	
	$code = isquotetion($texttosearch);
	if($code)  $texttosearch = $code['code'];
	$pagetitle = $texttosearch." (".gettransname($db,$reftrans,'true').") | Szentírás"; 
	 
	if(!is_array($code)) {
	/*
	 * HA SZÖVEGET KERES
	 */
		$title .= "<a href='".$baseurl."index.php?q=searchbible'>Keresés</a>: „".$texttosearch."”\n";
		$content .= "<span class='alap'>Fordítás: <a href='".$baseurl."index.php?q=showbible&reftrans=".$reftrans."'>". dlookup($db,"name","tdtrans","did=$reftrans") . "</a></span><br>\n";
		list($res1, $res2, $res3, $res4)=advsearchbible($db,$texttosearch,$reftrans,$offset,$rows);
		$tipps = get_tipps($texttosearch,$reftrans,$res2);	
		
		//HA EGY SZÓBÓL ÁLL
		if( ( $res2 > $min OR $res2 < $max OR $res2 < 0) AND count(explode(' ',$texttosearch)) == 1 ) {
			getSzinonimaTipp($texttosearch);
			//getSpellTipp($texttosearch); 
			//get több szó
		}/*
		// több szó
		elseif(   ) {
		} */
	
	
	 if ($res2 > 0) {
        $begin=$res3+1;
        if ($begin + $res4 > $res2 ) {
           $end = $res2;
        } else {
           $end = $begin + $res4 -1;
        }
		if($begin == 1) insert_stat($texttosearch,$reftrans,$res2);		
	
	    $content .= "<p class='kiscim'> $begin - $end. találat az összesen $res2-ből.</p>";
		foreach($tipps as $tipp) $content .= "<div id='tipp'><span class='hiba'>TIPP:</span> ".$tipp."</div>\n";
		$content .= showversesnextprev($script[0]."?q=searchbible&texttosearch=$texttosearch&reftrans=$reftrans", $res2, $res3, $res4,"&");	
        $content  .= showverses($res1,"index.php?q=showchapter",$reftrans);
        $content .= showversesnextprev($script[0]."?q=searchbible&texttosearch=$texttosearch&reftrans=$reftrans", $res2, $res3, $res4,"&");	
    
	} else {
	
		$content .= "<br><p>Nincs találat!</p>";
		foreach($tipps as $tipp) $content .= "<div id='tipp'><span class='hiba'>TIPP:</span> ".$tipp."</div>\n";
		insert_stat($texttosearch,$reftrans,-1);
	}
	/* END */
	} else {
	/*
	 * HA IGEHELYET KERES
	 */
		$title = "<a href='".$baseurl."index.php?q=showtrans&reftrans=".$code['reftrans']."'>".dlookup($db,"name","tdtrans","did=".$code['reftrans']."")."</a> <img src='".$fileurl."img/arrowright.jpg'> ".$code['code']."\n";
		
		$quotation = quotetion(array('verses','array','code'=>$code));
		
		foreach($tipps as $tipp) $content .= "<span class='hiba'>TIPP:</span> ".$tipp."<br>\n";
		
		$content .= "<br>".print_quotetion('verses')."<br>";
		
		if($error == array()) {
			insert_stat($texttosearch,$reftrans,0);
		} else {
			insert_stat($texttosearch,$reftrans,-1);
		}
		
	
		//$tipps = get_tipps($texttosearch,$reftrans,$res2);	
	/* END */ 
	 }
	 }
	
  
  function get_tipps($texttosearch, $reftrans, $results) {
	global $tipp;
	$return = array(); 
	if($results < 101) {
	$better = get_better($texttosearch,$reftrans,$results);
	if($better != array()) {
			global $baseurl; global $db;
			$trans = array(); foreach(db_query("SELECT * FROM tdtrans") as $list) $trans[$list['did']] = $list;
			foreach($better as $bet) {
			$return[] = "<a href='".$baseurl."index.php?q=searchbible&texttosearch=".$texttosearch."&reftrans=".$bet['reftrans']."' class=link>A ".$trans[$bet['reftrans']]['name']." fordításában több eredmény vár (".$bet['results']." találat)! </a>";
			}		
	}
	/*
	$jsonurl = "http://szolgaltatas.jezsu.hu:8083/tmp/hunspell/json.php?text=".$texttosearch;
	$json = file_get_contents($jsonurl,0,null,null);
	$json_output = json_decode($json);
	print_R($json_output);
	*/
	
	
	}
	$less = get_less($texttosearch,$reftrans,$results);
	if($less) {
			$return[] = $less;
	}
	if($results > 20) {
			$detail = get_more($texttosearch,$reftrans,$results);
			if($detail) $return[] = $detail;
	}
	return $return;
  }
  
  function getSzinonima($texttosearch,$max = 2) {
	$szinonima = array();
	/* opendir szinoníma szótárból *
	$url = "http://opendir.hu/szinonima-szotar/api.php?t=json&q=".iconv("ISO-8859-2",'UTF-8',$texttosearch);
	$file = file_get_contents($url,0);
	if($file != iconv("ISO-8859-2",'UTF-8','Nincs találat!')) {
		$json = json_decode($file,true); $c = 1;
	
		foreach($json as $k=>$i) {
			$szo = iconv('UTF-8',"ISO-8859-2",$k);
			if($szo != $texttosearch) {
				$szinonima[] = $szo;
				$c++; if($c > $max) break;
			}
		}
	}
	/* saját adatbázisból */
	$query = "SELECT * FROM szinonimak WHERE tipus = 'szo' AND (szinonimak LIKE '%|".$texttosearch."|%' OR szinonimak LIKE  '%|".$texttosearch.":%');";
	$result = db_query($query);
	if(is_array($result)) foreach($result as $r) {
		$szin = explode('|',$r['szinonimak']);
		foreach($szin as $sz) {
			$s = explode(':',$sz);
			if($s[0] != '' AND $s[0] != $texttosearch AND !in_array($s[0],$szinonima) ) {
				global $reftrans;
				if((isset($s[1]) AND $s[1] != 0) OR !isset($s[1]) OR (isset($s[1]) AND $s[1] == $reftrans ))
					$szinonima[] = $s[0];
				}
		}
	}
	
	
	return $szinonima;
  }
  function getSzinonimaTipp($texttosearch) {
	global $baseurl; global $reftrans;
	$szinonima = getSzinonima($texttosearch);
	$return = "Talán próbáld más szavakkal: ";
	$c = 1;
	foreach($szinonima as $szin) {
		$return .= " <a href='".$baseurl."index.php?q=searchbible&texttosearch=".$szin."&reftrans=".$reftrans."' class=link>".$szin."</a>";		
		if($c<count($szinonima)) $return .= ',';
		$c++;
	}
	$return .= '!';
	if($szinonima != array()) { global $tipps; $tipps[] = $return; return true; }
	else return false;
	
  }
  
  function getSpell($texttosearch,$max = 1) {
  	$hunspell = array();
	//$url = "http://szolgaltatas.jezsu.hu:8083/tmp/hunspell/json.php?text=".iconv("ISO-8859-2",'UTF-8',$texttosearch);
	$url = "http://szolgaltatas.jezsu.hu:8083/tmp/hunspell/json.php?text=".$texttosearch;
	//print_R($url);
	$file = file_get_contents($url,0,null,null);
	if($file != iconv("ISO-8859-2",'UTF-8','Nincs találat!')) {
		$json = json_decode($file,true); $c = 1;
		print_r($json);
		if(isset($json[iconv("ISO-8859-2",'UTF-8',$texttosearch)])) 
			foreach($json[iconv("ISO-8859-2",'UTF-8',$texttosearch)] as $k=>$i) {
				$hunspell[] = iconv('UTF-8',"ISO-8859-2",$i);
				$c++; if($c > $max) break;
			}
	}
	return $hunspell;
  }
  function getSpellTipp($texttosearch) {
	global $baseurl; global $reftrans;
	$spell = getSpell($texttosearch,1);
	$return = "Nem így gondoltad: ";
	foreach($spell as $s) {
		$return .= " <a href='".$baseurl."index.php?q=searchbible&texttosearch=".$s."&reftrans=".$reftrans."' class=link>".$s."</a>";		
	}
	$return .= '?';
	if($spell != array()) { global $tipps; $tipps[] = $return; return true; }
	else return false;
  }
  
  function get_better($texttosearch, $reftrans, $results) {
	$return = array();
	if(!is_numeric($results )) $results = '0';
	$result = db_query("SELECT * FROM stats_search WHERE texttosearch = '".$texttosearch."' AND reftrans <> ".$reftrans." ORDER BY texttosearch, results DESC LIMIT 0,10");	
	// TODO: TODO!: annyit nézzen, ahány féle fordítás van!
	$stats = array();
	if(is_array($result)) foreach($result as $res) $stats[$res['reftrans']] = $res;
	
	$result = $stats;
	for($i=1;$i<4;$i++) {
		if(isset($result[$i]) AND $result[$i]['results'] > $results AND $i != $reftrans) $return[] =  array('reftrans'=>$i,'results'=>$result[$i]['results']);
		elseif(!isset($result[$i]) AND $i != $reftrans) {
			global $db;
			$res = advsearchbible($db,$texttosearch,$i);
			db_query("INSERT INTO stats_search VALUES ('".$texttosearch."',".$i.",".$res[1].",0);");
			if($res[1] > $results) $return[] = array('reftrans'=>$i,'results'=>$res[1]);
		}
	} 
	return $return;
  }
  
    function get_more($texttosearch, $reftrans, $results) {
		global $baseurl;
		$results = db_query("SELECT * FROM stats_search WHERE texttosearch regexp '".$texttosearch."' AND reftrans = ".$reftrans." AND results < ".$results." AND results > 0 ORDER BY texttosearch DESC, count DESC LIMIT 0,10");	
		$return = 'Próbálkozz hosszabb kifejezéssel! ';
		if(is_array($results)) {
			$return .= 'Például: ';
			foreach($results as $k => $result) {
				$return .= " <a href='".$baseurl."index.php?q=searchbible&texttosearch=".$result['texttosearch']."&reftrans=".$reftrans."' class='link'>".$result['texttosearch']." (".$result['results']." találat)</a>";
				if($k < (count($results)-1)) $return .= ', ';
				else $return .= '.';
			}
			
		}
		return $return;
	}
	
	function get_less($texttosearch, $reftrans, $results) {
		if(!is_numeric($results)) $results = 0;
	
		$return = false;
		global $baseurl;
		$words = explode(' ',$texttosearch);
		if(count($words)>1) {
			$where = '(';
			foreach($words as $k=>$word) {
				$where .= " texttosearch = '".$word."' ";
				if($k<(count($k))) $where .= 'OR ';
				else $where .= '';
			} $where .= ')';
				$results = db_query("SELECT * FROM stats_search WHERE ".$where." AND reftrans = ".$reftrans." AND results > ".$results." AND results > 0 AND results < 50 ORDER BY texttosearch DESC, count DESC LIMIT 0,10");	
		
			if(is_array($results)) {
				$return = 'Próbálkozz rövidebb kifejezéssel! Például: ';
				foreach($results as $k => $result) {
					$return .= " <a href='".$baseurl."index.php?q=searchbible&texttosearch=".$result['texttosearch']."&reftrans=".$reftrans."' class='link'>".$result['texttosearch']." (".$result['results']." találat)</a>";
					if($k < (count($results)-1)) $return .= ', ';
					else $return .= '.';
				}
			}
			return $return;
		}
		
		return;
	}

	function printSearchForm() {
		global $db;
		$return = "<p class='cim'>Keresés a Bibliában</p>";
		$return .= "<form action='index.php' method='get'>\n";
		$return .= "<input type='hidden' name='q' value='searchbible'>\n";
		$return .= displaytextfield("texttosearch",30,80,"","Keresendő:","alap");
		
		$return .= displayoptionlist("reftrans",1,listbible($db),"did","name","1","Fordítás:","alap");
		$return .= '<br><br>';
		//$return .= "<input type=reset value='Törlés' class='alap'> &nbsp;&nbsp;\n";
		$return .= "<input type=submit value='Küldés' class='alap'>\n";
		$return .= "</form>\n";
		return $return;
	}
	
?>