<?php
		$texttosearch = $text;
	$code = isquotetion($texttosearch,$reftrans);
	if($code)  $texttosearch = $code['code'];
	$pagetitle = $texttosearch." (".gettransname($reftrans,'true').") | Szentírás"; 

	/*
	 * HA IGEHELYET KERES
	 */
		$title = "<a href='".BASE."index.php?q=showtrans&reftrans=".$code['reftrans']."'>".dlookup("name","tdtrans","id=".$code['reftrans']."")."</a> <img src='".BASE."img/arrowright.jpg'> ".$code['code']."\n";
		
		$quotation = quotetion(array('verses','array','code'=>$code));
		
		foreach($tipps as $tipp) $content .= "<span class='hiba'>TIPP:</span> ".$tipp."<br>\n";
		
		$content .= "<br>".print_quotetion('verses')."<br><br>";

		$query = "SELECT gepi FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans  WHERE ".DBPREF."tdverse.trans = ".$code['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$code['book']."' LIMIT 1";
		$result = db_query($query);
		if($result[0]['gepi']!='') {
			$query = "SELECT ".DBPREF."tdtrans.*, ".DBPREF."tdtrans.abbrev as transabbrev,".DBPREF."tdbook.abbrev,".DBPREF."tdbook.url,".DBPREF."tdverse.trans FROM ".DBPREF."tdverse 
				LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans 
				LEFT JOIN ".DBPREF."tdtrans ON ".DBPREF."tdverse.trans = ".DBPREF."tdtrans.id WHERE gepi = ".$result[0]['gepi']."
				 GROUP BY ".DBPREF."tdtrans.id
				 ORDER BY ".DBPREF."tdtrans.denom, ".DBPREF."tdtrans.name";

			$results = db_query($query);
			if(count($results)>1) {
			foreach($results as $result) {
			
				$transcode = preg_replace('/ /','',preg_replace("/^".$code['book']."/",$result['abbrev'],$code['code']));
				$url = BASE.$result['transabbrev']."/".$transcode;
				
				if($transcode = $code['code'] AND $code['reftrans'] == $result['trans']) $style = " style=\"background-color:#9DA7D8;color:white;\" "; else $style = '';
				$change = "<a href=\"".$url."\" ".$style." class=\"button minilink\">".$result['transabbrev']."</a> \n";
				$content .= $change;//echo $url;
			} }
			$content .= '<br>';
		}
		
		if($error == array()) {
			insert_stat($texttosearch,$reftrans,1,'quote');
		} else {
			insert_stat($texttosearch,$reftrans,-1,'quote');
		}
		
	
		//$tipps = get_tipps($texttosearch,$reftrans,$res2);	
	/* END */ 

?>