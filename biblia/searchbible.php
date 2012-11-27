<?php
session_start();

  /*
   * Default values
   */
  $min = 20;
  $max = 100;
  if (isset($_REQUEST['offset'])) $offset = $_REQUEST['offset']; else $offset = 0;
  if (isset($_REQUEST['rows'])) $rows = $_REQUEST['rows']; else $rows = 50;
  if (isset($_REQUEST['reftrans'])) $reftrans = $_REQUEST['reftrans']; else $reftrans = 1;
  
  require("../include/design.php");
  require("../include/biblemenu.php");
  require("../include/bibleconf.php");
  require("../include/biblefunc.php");
  
  require("JSON.php"); /* PHP 5.2 >= esetén */

  portalhead("Keresés eredményei");
  bibleleftmenu();

  
  
  if(!isset($_REQUEST['texttosearch']) OR $_REQUEST['texttosearch'] == '') {  printSearchForm();  portalfoot(); exit; }
  
  
  
  if(mb_detect_encoding($_REQUEST['texttosearch'],'UTF-8, ISO-8859-2') == 'UTF-8') $texttosearch = iconv('UTF-8',"ISO-8859-2",$_REQUEST['texttosearch']);
  else $texttosearch = $_REQUEST['texttosearch'];
	
  	/* a db_query is ebbenvan */
	$_GET['quotation'] = $texttosearch;
	include 'quote.php';
	
   $script = explode("?",$_SERVER['REQUEST_URI']);
   
   $texttosearch = preg_replace("/_/"," ",$texttosearch);
   $tipps = array();
	 
	echo "<p class='cim'>A keresés eredményei<p>\n";
    echo "<span class='alap'><b> Keresõkifejezés:</b>\n";
    echo "<strong>$texttosearch</strong>";
    echo "; fordítás: ". dlookup($db,"name","tdtrans","did=$reftrans") . " </span><br>\n";
	
	/*nem ismeri fel az alrészese izéket a-v*/
	preg_match('/([1-4]{0,1})(| )([\w]{0,10})(| )([0-9,.;-]{1,20})/',$texttosearch,$matches);
	if($matches == array()) {
	/*
	 * HA SZÖVEGET KERES
	 */
		
		list($res1, $res2, $res3, $res4)=advsearchbible($db,$texttosearch,$reftrans,$offset,$rows);
		
		$tipps = get_tipps($texttosearch,$reftrans,$res2);	
		//db_query("SELECT * FROM searcgstats WHERE");
	
		//HA EGY SZÓBÓL ÁLL
		if( ( $res2 > $min OR $res2 < $max OR $res2 < 0) AND count(explode(' ',$texttosearch)) == 1 ) {
			getSzinonimaTipp($texttosearch);
			//getSpellTipp($texttosearch); 
			//get több szó
		}/*
		elseif( /* több szó  ) {
		
		
		} */
	
	foreach($tipps as $tipp) echo "<span class='hiba'>TIPP:</span> ".$tipp."<br>\n";
	
	 if ($res2 > 0) {
        $begin=$res3+1;
        if ($begin + $res4 > $res2 ) {
           $end = $res2;
        } else {
           $end = $begin + $res4 -1;
        }
		if($begin == 1) insert_stat($texttosearch,$reftrans,$res2);		
	
	    echo "<p class='kiscim'> $begin - $end. találat az összesen $res2-bõl.</p>";
        showverses($res1,"showchapter.php",$reftrans);
        showversesnextprev($script[0]."?texttosearch=$texttosearch&reftrans=$reftrans", $res2, $res3, $res4,"&");	
    } else {
	
		echo "<br>Nincs találat!";
		insert_stat($texttosearch,$reftrans,-1);
	}
	/* END */
	} else {
	/*
	 * HA IGEHELYET KERES
	 */
				
		$quotation = quotetion('verses','array');
		foreach($tipps as $tipp) echo "<span class='hiba'>TIPP:</span> ".$tipp."<br>\n";
		
		echo "<br>".print_quotetion('verses')."<br>";
		
		if($error == array()) {
			insert_stat($texttosearch,$reftrans,0);
		} else {
			insert_stat($texttosearch,$reftrans,-1);
		}
		
	
		//$tipps = get_tipps($texttosearch,$reftrans,$res2);	
	 /* END */ 
	 }
  portalfoot();

  function insert_stat($texttosearch, $reftrans, $results) {
	global $tipps;
	$tipp = strip_tags(implode('\n',$tipps));
	
	db_query("INSERT INTO stats_texttosearch VALUES ('".$texttosearch."',".$reftrans.",'".date('Y-m-d H:i:s')."',".$results.",'".session_id()."','".$tipp."');");
	$result = db_query("SELECT * FROM stats_search WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans." ORDER BY texttosearch, count DESC LIMIT 0,1",1);
	if(is_array($result))
		db_query("UPDATE stats_search SET count = ".($result[0]['count']+1).", results = ".$results." WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans.";",1);
	else
		db_query("INSERT INTO stats_search VALUES ('".$texttosearch."',".$reftrans.",".$results.",1);");
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
			$return[] = "<a href='".$baseurl."searchbible.php?texttosearch=".$texttosearch."&reftrans=".$bet['reftrans']."' class=link>A ".$trans[$bet['reftrans']]['name']." fordításában több eredmény vár (".$bet['results']." találat)! </a>";
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
	/* opendir szinoníma szótárból */
	$url = "http://opendir.hu/szinonima-szotar/api.php?t=json&q=".iconv("ISO-8859-2",'UTF-8',$texttosearch);
	$file = file_get_contents($url,0,null,null);
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
		$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$szin."&reftrans=".$reftrans."' class=link>".$szin."</a>";		
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
		$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$s."&reftrans=".$reftrans."' class=link>".$s."</a>";		
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
		$return = 'Próbálkozz több keresõszóval! ';
		if(is_array($results)) {
			$return .= 'Például: ';
			foreach($results as $k => $result) {
				$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$result['texttosearch']."&reftrans=".$reftrans."' class='link'>".$result['texttosearch']." (".$result['results']." találat)</a>";
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
				$return = 'Próbálkozz kevesebb keresõ szóval! Például: ';
				foreach($results as $k => $result) {
					$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$result['texttosearch']."&reftrans=".$reftrans."' class='link'>".$result['texttosearch']." (".$result['results']." találat)</a>";
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
		echo "<p class='cim'>Keresés a Bibliában</p>";
		echo "<form action='searchbible.php' method='get'>\n";
		displaytextfield("texttosearch",30,40,"","Keresendõ:","alap");
		echo "<br>\n";
		displayoptionlist("reftrans",5,listbible($db),"did","name","1","Fordítás:","alap");
		echo "<br>\n";
		echo "<input type=reset value='Törlés' class='alap'> &nbsp;&nbsp;\n";
		echo "<input type=submit value='Küldés' class='alap'>\n";
		echo "</form>\n";
	}
	
?>