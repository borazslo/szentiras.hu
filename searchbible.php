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
		$content .= "<input type=hidden name='texttosearch'  value='".$texttosearch."' class='alap'>";
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
			AND resultupdated > '".date('Y-m-d H:i:s',strtotime("-".getvar('cache_mysql_lifetime')))."'
		ORDER BY resultcount DESC
		LIMIT 1";
	$result = db_query($query);
	if(isset($result[0]) AND getvar('cache_on') == 'on' AND $result[0]['searchcount']>1) {
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
			texttosearch = '".$texttosearch."'
			AND reftrans = '".$trans['id']."'
			AND searchtype = '".$searchby."'
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
	$tipps = array_unique($tipps);
	foreach($tipps as $tipp) 
		$content .= '<div id="tipp"><font color="red">TIPP:</font> '.$tipp.'</div>';
	
	if($count > $rows) $content .= showversesnextprev($GLOBALS['tdtrans_abbrev'][$transid]."/".$texttosearch, $count, $page, $rows, '?');
	
	$content .= "<div class=\"results2\">";
	foreach($verses as $verse) {
		if($verse != '') {
		$content .= "<img src='".BASE."img/arrowright.jpg' alt='->' title ='".$verse['point']."'> ";
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

	?>