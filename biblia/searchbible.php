<?php
session_start();

  require("../include/design.php");
  require("../include/biblemenu.php");
  require("../include/bibleconf.php");
  require("../include/biblefunc.php");

  portalhead("Keresés eredményei");
  bibleleftmenu();

  if (isset($_REQUEST['texttosearch']) AND isset($_REQUEST['reftrans'])) {
	$texttosearch = $_REQUEST['texttosearch'];
	$reftrans = $_REQUEST['reftrans'];

   $script = explode("?",$_SERVER['REQUEST_URI']);
   if (empty($offset)) {$offset = 0;}
   if (empty($rows)) {$rows=50;}

   $texttosearch = preg_replace("/_/"," ",$texttosearch);
	
	//iconv('UTF-8','ISO-8892-2',$texttosearch);
    
	echo "<p class='cim'>A keresés eredményei<p>\n";
    echo "<span class='alap'><b> Keresõkifejezés:</b><br>\n";
    echo "Keresendõ: $texttosearch";
    echo "; fordítás: ". dlookup($db,"name","tdtrans","did=$reftrans") . " </span><br>\n";
	
    list($res1, $res2, $res3, $res4)=advsearchbible($db,$texttosearch,$reftrans,$offset,$rows);
	//db_query("SELECT * FROM searcgstats WHERE");
	
	$_GET['quotation'] = $texttosearch;
	include 'quote.php';
	
	$tipps = get_tipps($texttosearch,$reftrans,$res2);	
	
	
	
    if ($res2 > 0) {
        $begin=$res3+1;
        if ($begin + $res4 > $res2 ) {
           $end = $res2;
        } else {
           $end = $begin + $res4 -1;
        }
		if($begin == 1) insert_stat($texttosearch,$reftrans,$res2);		
	
		foreach($tipps as $tipp)
			echo "<span class='hiba'>TIPP:</span> ".$tipp."<br>\n";
		
		
        echo "<p class='kiscim'> $begin - $end. találat az összesen $res2-bõl.</p>";
        showverses($res1,"showchapter.php",$reftrans);
        showversesnextprev($script[0]."?texttosearch=$texttosearch&reftrans=$reftrans", $res2, $res3, $res4,"&");
		
		
    } else {
			
		
		echo "<br>".quotetion('verses')."<br>";
		if($error == array()) {
			insert_stat($texttosearch,$reftrans,0);
		} else {
			insert_stat($texttosearch,$reftrans,-1);
		}
		//echo "Nincs találat!<br>";
    }
  }
  else {
	echo "<p class='cim'>Keresés a Bibliában</p>";

    echo "<form action='searchbible.php' method='get'>\n";

    /* displaytextfield ($name,$size,$maxlength,$value,$comment,) */
    /* displaytextarea ($name,$cols,$rows,$value,$comment) */
    /* displayoptionlist($name,$size,$rs,$valuefield,$listfield,$default,$comment) */

    displaytextfield("texttosearch",30,40,"","Keresendõ:","alap");
    echo "<br>\n";
    displayoptionlist("reftrans",5,listbible($db),"did","name","1","Fordítás:","alap");
    echo "<br>\n";
    echo "<input type=reset value='Törlés' class='alap'> &nbsp;&nbsp;\n";
    echo "<input type=submit value='Küldés' class='alap'>\n";
    echo "</form>\n";
  
  }


  portalfoot();

  function insert_stat($texttosearch, $reftrans, $results) {
	global $tipps;
	$tipp = strip_tags(implode('\n',$tipps));
	
	db_query("INSERT INTO stats_texttosearch VALUES ('".$texttosearch."',".$reftrans.",'".date('Y-m-d H:i:s')."',".$results.",'".session_id()."','".$tipp."');");
	$result = db_query("SELECT * FROM stats_search WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans." ORDER BY texttosearch, count DESC LIMIT 0,1",1);
	if(is_array($result))
		db_query("UPDATE stats_search SET count = ".($result[0]['count']+1)." WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans.";",1);
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
			$return[] = "<a href='".$baseurl."searchbible.php?texttosearch=".$texttosearch."&reftrans=".$bet['reftrans']."' class=link>A ".$trans[$bet['reftrans']]['name']." fordításban több eredmény vár (".$bet['results']." találat)! </a>";
			}		
	}
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
		if(is_array($results)) {
			$return = 'Próbálkozz több keresõ szóval! Például: ';
			foreach($results as $k => $result) {
				$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$result['texttosearch']."&reftrans=".$reftrans."' class='link'>".$result['texttosearch']." (".$result['results']." találat)</a>";
				if($k < (count($results)-1)) $return .= ', ';
				else $return .= '.';
			}
			return $return;
		}
		return;
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

?>