<?php
$pagetitle = 'Keresés | Szentírás';
if (isset($_REQUEST['page'])) $page = $_REQUEST['page']; else $page = 1;
if (isset($_REQUEST['rows'])) $rows = $_REQUEST['rows']; else $rows = 20;
if (isset($_REQUEST['searchby'])) $_SESSION['searchby'] = $_REQUEST['searchby']; 
if (isset($_SESSION['searchby'])) $searchby = $_SESSION['searchby'];
else $searchby = 'bychapter';

$transid = $reftrans;

$texttosearch = $text;
if (isset($_REQUEST['in'])) $texttosearch = $texttosearch." in:".$_REQUEST['in'];

if($texttosearch == '') { 
		$content .= printSearchForm(); 
} else {

/* CÍM és felső sor */
$pagetitle = $texttosearch." (".gettransname($reftrans,'true').") | Szentírás"; 
$title .= "<a href='".BASE."kereses'>Keresés</a>: „".$texttosearch."”\n";
$content .= "<form action='".BASE."index.php' method='get'>\n";
		$content .= "<input type='hidden' name='q' value='searchbible'>\n";
		$content .= "<input type=hidden name='texttosearch' id='texttosearch' value='".$texttosearch."' class='alap'>";
		$content .= "<span class='alap'>Fordítás:</span> ";					
		$content .= "<select id='reftrans' name='reftrans' class='alap' onChange=\"this.form.submit();\">";
		foreach($GLOBALS['tdtrans'] as $trans) {
				$content .= "<option value=\"".$trans['id']."\"";
				if($trans['id'] == $transid) $content .= ' selected ';
				$content .= ">".$trans['name']."</option>";
		}
		$content .= "</select>";		
		$content .= "<br><br><span class='alap'>Csoportosítás: </span>";					
		$content .= "<select id='searchby' name='searchby' class='alap' onChange=\"this.form.submit();\">";
		foreach(array('bychapter'=>'fejezetenként','byverse'=>'versenként') as $byvalue => $bytext) {
				$content .= "<option value=\"".$byvalue."\"";
				if($searchby == $byvalue) $content .= ' selected ';
				$content .= ">".$bytext."</option>";
		}
		$content .= "</select>";		
		$content .= "</form>\n";

/* hátha, már kerestük ezt vagy nem*/
$query = "
		SELECT resultarray, resultcount
		FROM ".DBPREF."stats_search
		WHERE
			searchtype = '".$searchby."'
			AND rows = '".$rows."'
			AND page = '".$page."'
			AND reftrans = '".$transid."'
			AND texttosearch = '".$texttosearch."'
			AND resultupdated > '".date('Y-m-d H:i:s',strtotime("-".getvar('cache_lifetime')))."'
		ORDER BY resultcount DESC
		LIMIT 1";
	$result = db_query($query);
	if(isset($result[0]) AND getvar('cache_on') == 'on') {
		$verses = unserialize($result[0]['resultarray']);
		$count = $result[0]['resultcount'];
	} else {
		$tmp = search($texttosearch,$reftrans, $rows);
		$count = count($tmp);
		$c = 1; $verses = array();
		foreach($tmp as $k=>$t) {
			if($c > ($page - 1) * $rows ) {
				$verses[$k] = $t;
			}
			if( $c >= $page * $rows) break;
			$c++;
		}
	
	}
	insert_stat($texttosearch, $reftrans, $verses);

	
/* JOBB felső sarok, az-az az alternatívák */
$share .= "<div id=\"share\">";
foreach($GLOBALS['tdtrans'] as $trans) {
	$query = "
		SELECT resultcount 
		FROM ".DBPREF."stats_search
		WHERE
			searchtype = '".$searchby."'
			AND reftrans = '".$trans['id']."'
			AND texttosearch = '".$texttosearch."'
		ORDER BY resultcount DESC
		LIMIT 1";
	$result = db_query($query);
	if(isset($result[0])) $upper = "<sup>(".$result[0]['resultcount'].")</sup>";
	else $upper = '';
	
	$share .= "<a href=\"".BASE.$trans['abbrev']."/".urlencode($texttosearch)."\" class=\"button minilink\">".$trans['abbrev'].$upper."</a>";
}
$share .= "</div>";

	
	
/* lássuk, mi az eremény */	
	$content .= "<br/><p class='title'><strong>".$count." találat</strong></p>";
	/*tippek*/
	getSzinonimaTipp($texttosearch);
	getIdezetTipp($texttosearch);
	foreach($tipps as $tipp) 
		$content .= '<div id="tipp"><font color="red">TIPP:</font> '.$tipp.'</div>';
	
	if($count > $rows) $content .= showversesnextprev($GLOBALS['tdtrans_abbrev'][$transid]."/".$texttosearch, $count, $page, $rows, '?');
	
	$content .= "<div class=\"results2\">";
	foreach($verses as $verse) {
		if($verse != '') {
		$content .= "<img src='".BASE."img/arrowright.jpg' title ='".$verse['point']."'> ";
		$vfirst = array_shift(array_values($verse['verses']));
	    $szep = $GLOBALS['tdbook_abbrev'][$transid][substr($vfirst['gepi'],0,3)]." ".(int) substr($vfirst['gepi'],3,3);
		$content .= "<a href=\"".preg_replace('/ /i','',BASE.$GLOBALS['tdtrans_abbrev'][$transid]."/".$szep."#".(int) substr($vfirst['gepi'],6,3))."\" class=\"link\">".$szep."</a> - ";
		$vlast = 10000000;
		foreach($verse['verses'] as $gepi => $v) {
			//$content .= $v['verse'];	
			$vnum = (int) substr($v['gepi'],6,3);
			if($vnum > $vlast + 1) $content .= " [...] ";
			$vlast = $vnum;
			$content .= showverse($v['gepi'],$transid,'verseonly');
		}
		$content .= '<br>';
	
	}}
	$content .= "</div>";
	
	if($count > $rows) $content .= showversesnextprev($GLOBALS['tdtrans_abbrev'][$transid]."/".$texttosearch, $count, $page, $rows, '?');
	//$content .= "<pre>".print_r($verses,1)."</pre>";	
	
}

function getIdezetTipp($texttosearch) {
	global $reftrans, $tipps;
	foreach($GLOBALS['tdtrans'] as $trans) {
		foreach($GLOBALS['tdbook'][$trans['id']] as $book) {
			$pattern = "/^(".implode("|",array($book['abbrev'])).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";	
			if(preg_match($pattern,$texttosearch,$matches))  {
				if(isset($matches[1])) {
						$jokonyv = $GLOBALS['tdbook'][$reftrans][$book['id']]['abbrev'];
						$jotext = preg_replace('/^'.$matches[1].'(.*?)$/',$jokonyv.'$1',$texttosearch);
						$tipps[] = 'Így értetted: <a href="'.BASE.$GLOBALS['tdtrans_abbrev'][$reftrans]."/".$jotext.'">'.$jotext.'</a>?';
				
				}
			}
		}
	}
}

function getSzinonima($texttosearch,$max = 2) {
	$szinonima = array();
	global $reftrans;
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
	$query = "SELECT * FROM ".DBPREF."szinonimak WHERE tipus = 'szo' AND (szinonimak LIKE '%|".$texttosearch."|%' OR szinonimak LIKE  '%|".$texttosearch.":%');";
	$result = db_query($query);
	if(is_array($result)) foreach($result as $r) {
		$szin = explode('|',$r['szinonimak']);
		foreach($szin as $sz) {
			$s = explode(':',$sz);
			if($s[0] != '' AND $s[0] != $texttosearch AND !in_array($s[0],$szinonima) ) {
				global $reftrans;
				if(!isset($s[1]) OR (isset($s[1]) AND $s[1] == $reftrans ))
					global $searchby;
					$query = "
						SELECT resultcount 
						FROM ".DBPREF."stats_search
						WHERE
							searchtype = '".$searchby."'
							AND reftrans = '".$reftrans."'
							AND texttosearch = '".$s[0]."'
						ORDER BY resultcount DESC
						LIMIT 1";
					$result = db_query($query);	
			
					if(isset($result[0]) AND $result[0]['resultcount'] > 0) {
						$szinonima[] = array('szo'=>$s[0],'resultcount'=>$result[0]['resultcount']);
					} elseif(!isset($result[0])) {
					
						$szinonima[] = array('szo'=>$s[0],'resultcount'=>0);
					} else {
					
					}
				}
		}
	}
	
	
	return $szinonima;
  }
  function getSzinonimaTipp($texttosearch) {
	 global $reftrans;
	$szinonima = getSzinonima($texttosearch);
	$return = "Talán próbáld más szavakkal: ";
	$c = 1;
	foreach($szinonima as $szin) {
		if($szin['resultcount']>0) $extra = ' <sup>('.$szin['resultcount'].')</sup>';
		else $extra = '';
		$return .= " <a href='".BASE.$GLOBALS['tdtrans_abbrev'][$reftrans]."/".$szin['szo']."' class=link>".$szin['szo'].$extra."</a>";		
		if($c<count($szinonima)) $return .= ',';
		$c++;
	}
	$return .= '!';
	if($szinonima != array()) { global $tipps; $tipps[] = $return; return true; }
	else return false;
	
  }

function printSearchForm() {

		global $reftrans, $transid;
		$return = "<p class='cim'>Keresés a Bibliában</p>";
		$return .= "<form action='".BASE."index.php' method='get'>\n";
		$return .= "<input type='hidden' name='q' value='searchbible'>\n";
		
		$return .= "<span class='alap'>Keresendő:</span><br/>";
		$return .= "<input type=text name='texttosearch' onkeyup=\"suggest(this.value);\" id='texttosearch' size=30 maxlength=80 value='".$texttosearch."' class='alap'>";
		$return .= '<div id="suggestions" class="suggestionsBox" style="display: none;">
					<div id="suggestionsList" class="suggestionList"></div>
					</div>';

		$return .= "<span class='alap'>Szűkítés:</span><br/>";					
		$return .= "<select id='reftrans' name='reftrans' class='alap' onChange=\"change_reftrans();\">";
		foreach($GLOBALS['tdtrans'] as $trans) {
				$return .= "<option value=\"".$trans['id']."\"";
				if($trans['id'] == $transid) $return .= ' selected ';
				$return .= ">".$trans['name']."</option>";
		}
		$return .= "</select>";
		
		$return .= " <select id='in' name='in' class='alap'>";
		$return .= "<option value=''>mind</option>";
		$return .= "<option value='Ószöv'>Ószövetség</option>";
		$return .= "<option value='Újszöv'>Újszövetség</option>";
		foreach($GLOBALS['tdtrans'] as $trans) {
			foreach($GLOBALS['tdbook'][$trans['id']] as $book) {
				$return .= "<option class=\"trans trans".$trans['id']."\" value=\"".$book['abbrev']."\"";
				if($trans['id'] != $transid) $return .= " style=\"display:none\" ";
				$return .= ">".$book['abbrev']."</option>";
			}
		}
		$return .= "</select>";
		
		$return .= "<br><br><span class='alap'>Csoportosítás: </span><br/>";					
		$return .= "<select id='searchby' name='searchby' class='alap'>";
		foreach(array('bychapter'=>'fejezetenként','byverse'=>'versenként') as $byvalue => $bytext) {
				$return .= "<option value=\"".$byvalue."\"";
				if($searcby == $byvalue) $return .= ' selected ';
				$return .= ">".$bytext."</option>";
		}
		$return .= "</select><br/><br/>";
		//$return .= "<input type=reset value='Törlés' class='alap'> &nbsp;&nbsp;\n";
		$return .= "<input type=submit value='Keresés' class='alap'>\n";
		$return .= "</form>\n";
		
		$return .= <<<EOD
			<p class="kiscim">A keresőről</p>
			<p><i>Majd egyszer újra megírom, most hogy *** elveszett</i></p>
		
EOD;
		
		//'<div id="tipp"><font color="red">TIPP:</font> a keresét le lehet szűkíteni egy-egy könyvre vagy az Ószövetségre/Újszövetségre a keresés végére írt <strong>in:<i>könyvrövidítés</i></strong> formával! Például: <A href="http://szentiras.hu/SZIT/%C3%B6r%C3%B6m%20in:Lk">öröm in:Lk</a></div>';
		
		return $return;
	}
?>