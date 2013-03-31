<?php

function isquotetion($text,$forcedtrans = false) {
	/* Ékezetes kis-nagybetű hiábánál false-al tér vissza!*/
	global $db,$reftrans;
	
	$text = preg_replace('/ /','',$text);
	
	if(preg_match('/([0-9]{1,3})(.|)(zsoltar|zsoltár)/i',$text,$match)) {
		$text = $match[3].$match[1];
	}
	
	/* összeszedjük a lehetséges könyv rövidítéseket */
	$rs = $db->execute("SELECT abbrev, reftrans FROM tdbook ORDER BY reftrans DESC");
	do {
		if($rs->fields['abbrev'] != '') {
			$books[$rs->fields['reftrans']][] = preg_replace('/ /','',$rs->fields['abbrev']);
			$abbrevs[preg_replace('/ /','',$rs->fields['abbrev'])] = preg_replace('/ /','',$rs->fields['abbrev']);
			$pattern = '/^'.preg_replace('/ /','',$rs->fields['abbrev']).'([0-9]{1,2}|$)/i';
			$text = preg_replace($pattern,preg_replace('/ /','',$rs->fields['abbrev']).'$1',$text);
		}
        $rs->nextRow();
	 } while (!$rs->EOF);
	$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";
	
	if(preg_match($pattern,$text,$matches))  {
	//echo "<pre>"; print_R($matches);	
	$book = $matches[1];
		if($forcedtrans != false AND in_array($book,$books[(int) $forcedtrans])) {
			$reftrans = $forcedtrans;
		}
		elseif(!in_array($book,$books[$reftrans])) {
			for($i=5;$i>0;$i--) {
				if(is_array($books[$i]) AND $i != $reftrans AND in_array($book,$books[$i])) {
					$reftrans = $i;
				}
			}
		}
		$return = $text;
	} else  {	
		$pattern2 = '/(^[1-5]{0,1}[^\d]{1,20}[1-5]{0,1})/';
		preg_match($pattern2,$text,$matches);
		if(isset($matches[1])) $tmps[] = $matches[1];
		
		$pattern2 = '/(^[1-5]{0,1}[^\d]{1,20})/';
		preg_match($pattern2,$text,$matches);
		if(isset($matches[1])) $tmps[] = $matches[1];
		
		foreach($tmps as $tmp) {
		$select = "SELECT * FROM szinonimak WHERE tipus = 'konyv' AND (binary szinonimak LIKE '%|".preg_replace('/ /','',$tmp)."|%' OR binary  szinonimak LIKE  '%|".preg_replace('/ /','',$tmp).":%');";
		$result = db_query($select); $szinonima = array();
		if(is_array($result)) foreach($result as $r) {
			$szinonima = array();
			$szin = explode('|',$r['szinonimak']);
			foreach($szin as $sz) {
				$s = explode(':',$sz);
				if($s[0] != '' AND $s[0] != $tmp AND !in_array($s[0],$szinonima) ) {
					if(isset($s[1]) AND $s[1] == $reftrans) {
						$szinonima[] = $s[0];
					}
				}
			}
		}
		foreach($szinonima as $szin) {
			$text = preg_replace('/^'.$tmp.'/',$szin,$text);
			if(preg_match($pattern,$text,$matches)) {
				$return = $text;
			} else {				
			}
		}
		}			
	}
	if(isset($return)) {
		//echo "<pre>";
		$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";
		preg_match($pattern,$text,$matches);
		
		$quote['book'] = $matches[1];
		$quote['code'] = preg_replace("/^(".implode("|",$abbrevs).")/",'$1 ',$text);
		$quote['reftrans'] = $reftrans;
		if(preg_match("/^(".implode("|",$abbrevs).")([0-9]{1,3})$/i",$text,$match)) {
			$query = "SELECT numv FROM tdverse WHERE reftrans = ".$quote['reftrans']." AND abbook = '".$quote['book']."' AND numch = ".$match[2]." ORDER BY ABS(numv) DESC LIMIT 1";
			$numv = db_query($query);
			if(is_array($numv) AND $numv[0]['numv']>0) {
				/* TODO: vesszőt vagy kettőspontot?? */
				$quote['tag'][1]['code'] = $match[2].",1-".$numv[0]['numv'];
				$quote['tag'][1]['numch'] = $match[2];
				for($s=1;$s<=$numv[0]['numv'];$s++)
				$quote['tag'][1]['numv'][] = $s;							
			}
		}
		
		$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})/i";
		$text = preg_replace($pattern,'$2',$text);
		$tags = explode(';',$text);
		foreach($tags as $key=>$tag) {
			$vesszo = count(explode(',',$tag));
			$kettospont = count(explode(':',$tag));
			if($vesszo > $kettospont) $case = $vesszo; else $case = $kettospont;
			switch ($case-1) {
				case 0:
					preg_match('/^([0-9]{1,3})-([0-9]{1,3})$/',$tag,$tmp);
					for($c=$tmp[1];$c<=$tmp[2];$c++) {
						$query = "SELECT numv FROM tdverse WHERE reftrans = ".$quote['reftrans']." AND abbook = '".$quote['book']."' AND numch = $c ORDER BY ABS(numv) DESC LIMIT 1";
						$numv = db_query($query);
						if(is_array($numv) AND $numv[0]['numv']>0) {
							/* TODO: vesszőt vagy kettőspontot?? */
							$quote['tag'][$key*100+$c]['code'] = $c.",1-".$numv[0]['numv'];
							$quote['tag'][$key*100+$c]['numch'] = $c;
							for($s=1;$s<=$numv[0]['numv'];$s++)
								$quote['tag'][$key*100+$c]['numv'][] = $s;							
						}
					}
					break;
				case 2:
					preg_match('/^([0-9]{1,3})(,|:)([0-9]{1,2})-([0-9]{1,3})(,|:)([0-9]{1,2})$/',$tag,$tmp);
					for($c=$tmp[1];$c<=$tmp[4];$c++) {
						$query = "SELECT numv FROM tdverse WHERE reftrans = ".$quote['reftrans']." AND abbook = '".$quote['book']."' AND numch = $c ORDER BY ABS(numv) DESC LIMIT 1";
						$numv = db_query($query);
						if(is_array($numv) AND $numv[0]['numv']>0) {
							if($c==$tmp[1]) $from = $tmp[3]; else $from = 1;
							if($c==$tmp[4]) $to = $tmp[6]; else $to = $numv[0]['numv'];
							$quote['tag'][$key*100+$c]['code'] = $c.$tmp[2].$from."-".$to;
							$quote['tag'][$key*100+$c]['numch'] = $c;
							for($s=$from;$s<=$to;$s++)
								$quote['tag'][$key*100+$c]['numv'][] = $s;				
						}
					}
					break;
				case 1:
					preg_match('/^([0-9]{1,3})(,|:)(.*?)$/',$tag,$tmp);
					$query = "SELECT numv FROM tdverse WHERE reftrans = ".$quote['reftrans']." AND abbook = '".$quote['book']."' AND numch = ".$tmp[1]." ORDER BY ABS(numv) DESC LIMIT 1";
					$numv = db_query($query);
					if(is_array($numv) AND $numv[0]['numv']>0) {
						$quote['tag'][$key*100]['numch'] = $tmp[1];
						$quote['tag'][$key*100]['code'] = $tag;
						
						$tmp2 = explode('.',$tmp[3]);
						foreach($tmp2 as $vers) {
							if(preg_match('/^([0-9]{1,2})-([0-9]{1,2})$/',$vers,$tmp3)) {
								for($s=$tmp3[1];$s<=$tmp3[2];$s++) {
									if($s<=$numv[0]['numv']) $quote['tag'][$key*100]['numv'][] = $s;
								}
							} else {
								if($vers<=$numv[0]['numv']) $quote['tag'][$key*100]['numv'][] = $vers;
							}
						}
					}
					break;
			}
		
		
		
		}
		//echo $forcedtrans."+".$reftrans."+".$quote['reftrans'];
		if($forcedtrans != NULL AND $forcedtrans != $quote['reftrans']) return FALSE;
		return $quote;
	} 
	return FALSE; 
}

function print_quotetion($args) {
	global $code;
	global $verses;
	global $error;
	global $query;
	
	$return = false;
	
	if(!is_array($args)) $args = array($args);
	
	if(in_array('title',$args)) $return .= "<p class='cim'>Idézet a szentírásból: $query<p>";
	if(in_array('form',$args)) $return .= print_form();

	//print_R($verses);
	if(in_array('verses',$args)) {
		$averses = $verses;
		$verses = print_verses($verses);
		
		if($pverses == "<span class='alap'></span>	") { $return .= "Nincs találat.";}
		else		{ $return .= $verses;
			
			global $meta;
			$description = preg_replace('/( [\d]+?)|([\d]+?)|(")/','',strip_tags($verses));
			if (strlen($description) > 300) {
				$stringCut = substr($description, 0, 300);
				$description = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
			}
			if (strlen($description) > 90) {
				$stringCut = substr($description, 0, 90);
				$datatext = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
			}
			$meta = '<meta property="og:description" content="'.$description.'">';
			global $texttosearch, $baseurl;
			$meta .= '<meta property="og:url" content="'.$baseurl.urlencode(preg_replace('/ /i','',$texttosearch)).'/" />';
			
			global $texttosearch;
			$meta .= '<meta property="og:title" content="Idézet a Szentírásból: '.$texttosearch.'">';
			
			global $share;
		
			$share .= '
				<div id="facebook">
					<a expr:share_url="data:post.url" href="http://www.facebook.com/sharer.php?" name="fb_share" rel="nofollow" type="button">Share</a>
					<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"/></script>
				</div>
				 ';
			
			$share .= '<div id="twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-related="jasoncosta" data-lang="hu"  data-count="none" data-hashtags="Biblia" data-url="'.$baseurl.urlencode(preg_replace('/ /','',$texttosearch)).'/" data-text="'.$datatext.'">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
			$share .= '<input style="padding:2px;" type="button" onclick="window.prompt(\'Rövid cím, amin elérhető ez az oldal:\',\''.$baseurl.urlencode(preg_replace('/ /','',$texttosearch)).'\');" value="rövid url">';
			//$share .= 'url: <a href="'.$baseurl.urlencode(preg_replace('/ /','',$texttosearch)).'">'.urlencode(preg_replace('/ /','',$texttosearch)).'</a>';
		}
	}
	$dids = array();
	global $db, $reftrans,$comments,$reftrans;
	foreach($averses as $verses) {
	if(isset($verses['did'])) {
		$query = "select * from tdverse where did = ".$verses['did']." order by did";
		$rs = $db->execute($query);
		list($list1,$list2,$list3)= listcomm($db,$rs,$reftrans);
		$rs = $list1;
		do {
			$dids[$rs->fields['did']] = " did = '".$rs->fields['did']."' ";
			$rs->nextRow();
		} while (!$rs->EOF);
	} else $return .= 'Mégsincs találat. Elnézést.'; }
	
	$query = "select * from tdcomm where (".implode(' OR ',$dids).") order by did";
	$rs = $db->execute($query);
	$comments .= showcomms($db, $rs, $reftrans,100,100);	

	
	if(in_array('errors',$args)) {$return .="<br>".print_errors($error); }
	return $return;
}
	
function print_form() {
		global $code;
		global $reftrans;
		global $query;
			
		global $base;
		$return = '<form name="input" action="'.$_SERVER['PHP_SELF'].'" method="get">
			<input type="text" name="quotation" value="'.$query.'" /><br />';
			
			$reftranss = db_query("SELECT * FROM tdtrans");
			
			/* RADIO BUTTON type *
			foreach($reftranss as $t) {
				$return .= '<input type="radio" name="reftrans" value="'.$t['did'].'" ';
				if($reftrans == $t['did']) $return .= "checked"; 
			$return .= '/> <span class="alap">'.$t['name'].' </span>';
			/* */
			
			/* SELECT type */
			$return .='<select name="reftrans">';
			foreach($reftranss as $t) {
				$return .= '<option value="'.$t['did'].'"';
				if($reftrans == $t['did']) $return .= " selected=\"selected\" "; 
				$return .= '>'.$t['name'].'</option>';
			}
			$return .='</select>';
			/* */
			
		$return .= '</form>';
		return $return;
}

function quotetion($argss)  {
	$args = array();
	foreach($argss as $k=>$v) {
		if(is_numeric($k)) {
			$args[] = $v;
		} else {
			${$k} = $v;
		}
	}
		
	if(!isset($code)) global $code;
	$kod = $code;
	$code = $code['code'];
	
	global $error;
	global $book;
	global $verses;
	global $reftrans;
	global $query;
	
	if(!in_array('html',$args) AND !in_array('array',$args) AND !in_array('json',$args) AND !in_array('xml',$args)) $args = array_merge($args,array('html'));
	if(!in_array('title',$args) AND !in_array('form',$args) AND !in_array('verses',$args)) $args = array_merge($args,array('title','form','verses'));
	
	
	$aa = array('html','json','xml','array','title','form','verses','errors');
	$tmp = array();	
	
	foreach($args as $k=>$i) {
		if(!in_array($i,$aa)) $tmp[] = $i;
	}
	foreach($tmp as  $t) {
		if(is_numeric($t)) $reftrans = $t;
		else $code = $query = $t;
	}

	$error = array();
	/* ellenőrzés, hogy semmi spéci karakter ne legyen benne */
	
	$c=0;
	//print_R($kod);
	if(isset($kod['tag'])) {
	foreach($kod['tag'] as $tag) {
		if(isset($tag['numv'])) {
		foreach($tag['numv'] as $numv) {
				//echo $kod['reftrans']." ".$kod['book']." ".$tag['numch'].":".$numv."<br>\n";
				$where = array(
					'reftrans'=>$kod['reftrans'],
					'abbook'=>$kod['book'],
					'numch'=>$tag['numch'],
					'numv'=>$numv);
				$w = array();
				foreach($where as $name=>$value) $w[] = " ".$name." = '".$value."'";
				$query = "SELECT * FROM tdverse WHERE ".implode(' AND ',$w)." LIMIT 1;";
				//echo $query."\n";
				$result = db_query($query);
				if(is_array($result)) $verses2[] = $result[0];
			}
		}
	}
	//print_R($verses2);
	//print_R($verses);
		$verses = $verses2;
		if($verses == '') $verses = array();
	} else $verses = array();
	
	if(in_array('html',$args)) return print_quotetion($args);	
	elseif(in_array('json',$args)) return json_encode(array('code'=>$kod['code'],'verses'=>$verses)); //,'errors'=>$errors));
	elseif(in_array('xml',$args)) return xml_encode(array('verses'=>$verses));
	elseif(in_array('array',$args)) return array('verses'=>$verses,'error'=>$error);
}



function add_verses($code,$start = false) {

	global $verses;
	global $book;
	global $reftrans;
	global $error;
	$tmp = explode(',',$code);
	$chapter = db_query("SELECT numch FROM tdchapter as c, tdbook as b WHERE c.reftrans = b.reftrans AND c.abbook = b.abbrev AND b.did = $book AND numch = ".$tmp[0]." LIMIT 0,1");
	if(!is_array($chapter)) { $error[] = "Nincs is ennyi fejezete a könyvnek."; $chapter = $tmp[0]; //return;
	}
	else $chapter = $chapter[0]['numch'];
	$s = 'maci';
	$tmp = explode('.',$tmp[1]);
	foreach($tmp as $k=>$t) {
		if(preg_match('/-/',$t)) {
			$nums = explode('-',$t);
			$lastv = db_query("SELECT lastv FROM tdchapter as c, tdbook as b WHERE c.reftrans = b.reftrans AND c.abbook = b.abbrev AND b.did = $book AND numch = ".$chapter." LIMIT 0,1");
			if($lastv[0]['lastv']<$nums[1]) {
			//echo "--".$lastv[0]['lastv']."--";
				if($lastv[0]['lastv'] == 0) {
					$error[] = "Nincs adat a versek számáról.";
					}
				else {
					$nums[1] = $lastv[0]['lastv'];
					$error[] = "There is not so many verses in the chapter";
					}
				}
			for($i=$nums[0];$i<=$nums[1];$i++) {
				if($s) { $start = true; $s = false;} else $start = false;
				$verses[] = get_verse($book,$chapter,$i,$reftrans,$start);
			}
		} else {
		if($s) { $start = true; $s = false;} else $start = false;
		$verses[] = get_verse($book,$chapter,$t,$reftrans,$start);
		}
	}
}

function get_verse($book,$chapter,$verse,$reftrans	= 1,$start = false) {
	global $error;
	
	$return = array();
	
	$query = "SELECT v.did as id,title, v.verse, b.* FROM tdverse as v, tdbook as b WHERE v.numv = $verse AND v.numch = $chapter AND v.reftrans = $reftrans AND b.abbrev = v.abbook AND b.reftrans = $reftrans AND b.did = $book LIMIT 0,1";
	//echo $query."<br>\n";
	$verses = db_query($query);
	if($verses != 1) { 
		$return['verse'] = $verses[0]['verse']; 
		$return['title'] = $verses[0]['title'];
		$return['did'] = $verses[0]['id'];
	}
	else { $error[] = "There is no verse found."; return false;}
	
	if($start != false) $return['start'] = true;
	$return['query'] = array("book"=>$book,"chapter"=>$chapter,"verse"=>$verse,"trans"=>$reftrans);
	
	return $return;
}

function print_errors($error) {
	$return =  "<span class=\"alap\"><font color='red'>";
	foreach($error as $er) $return .= $er."<br>";
	$return .= "</font></span>";
	return $return;
}

function print_verses($verses) {

	$return = "<span class='alap'>"; 
	foreach($verses as $k=>$verse) {
		if($verse != '') {
		
			$verse['verse'] = preg_replace('/ "/',' „',$verse['verse']);
			$verse['verse'] = preg_replace('/"( |,|\.|$)/','”$1',$verse['verse']);

		
			if($verse['title']!='') $return .= "<p class='kiscim'>".$verse['title']."</p>";
		if(array_key_exists('start',$verse) OR $k == 0 OR $numch != $verse['numch']) $return .= " <strong>".$verse['numch']."</strong> ";
		$return.= " <sup>".$verse['numv']."</sup>".$verse['verse']." ";
		$numch = $verse['numch'];
		}
	}
	$return .= "</span>	";
	return $return;
}

function xml_encode($array) {
	/*$doc = new DOMDocument();
	$fragment = $doc->createDocumentFragment();

	// adding XML verbatim:
	$xml = "Test &amp; <b> and encode </b> :)\n";
	$fragment->appendXML($xml);

	// adding text:
	$text = $xml;
	$fragment->appendChild($doc->createTextNode($text));

	// output the result
	echo $doc->saveXML($fragment);

	$xml = 'file';
	return $xml;*/
}


	
function setvar($name,$value) {
	$test = getvar($name);
	if( $test == false) {
		$query="INSERT INTO vars (name, value) VALUES ('$name','$value')";
	} else {	
		$query='UPDATE vars SET value = \''.$value.'\' WHERE name = \''.$name.'\'';
	}
	db_query($query);
}

function getvar($name) {
	$query="SELECT * FROM vars WHERE name = '".$name."' LIMIT 0,1";
	$result = db_query($query);
	
	if(!$result) return false; 
	return $result[0]['value'];
}

function insert_stat($texttosearch, $reftrans, $results) {
	global $tipps;
	$tipp = strip_tags(implode('\n',$tipps));
	db_query("SET NAMES 'utf8'");
	db_query("SET CHARACTER SET 'utf8'");
	if(isset($_REQUEST['texttosearch']) AND $_REQUEST['texttosearch'])
			db_query("INSERT INTO stats_texttosearch (texttosearch,reftrans,date,result,session,tipp,original,referrer)VALUES ('".$texttosearch."',".$reftrans.",'".date('Y-m-d H:i:s')."',".$results.",'".session_id()."','".$tipp."','".$_REQUEST['texttosearch']."','".$_SERVER['HTTP_REFERER']."');");
	else 
		db_query("INSERT INTO stats_texttosearch (texttosearch,reftrans,date,result,session,tipp,referrer) VALUES ('".$texttosearch."',".$reftrans.",'".date('Y-m-d H:i:s')."',".$results.",'".session_id()."','".$tipp."','".$_SERVER['HTTP_REFERER']."');");
		
	$result = db_query("SELECT * FROM stats_search WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans." ORDER BY texttosearch, count DESC LIMIT 0,1",1);
	if(is_array($result))
		db_query("UPDATE stats_search SET count = ".($result[0]['count']+1).", results = ".$results." WHERE texttosearch = '".$texttosearch."' AND reftrans = ".$reftrans.";",1);
	else
		db_query("INSERT INTO stats_search VALUES ('".$texttosearch."',".$reftrans.",".$results.",1);");
  }
  
?>
