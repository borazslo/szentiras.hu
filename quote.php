<?php



/*
* LINE 43, 44 körül reftrans átállítás meghallva, mert fostalicska
*/


function isquotetion($text,$forcedtrans = false) {
	
	/* Ékezetes kis-nagybetű hiábánál false-al tér vissza!*/
	global $db,$reftrans;
	//$forcedtrans = $reftrans;
	//echo $text; echo "-".$forcedtrans."<br/>";
	
	$text = preg_replace('/ /','',$text);
	
	if(preg_match('/([0-9]{1,3})(.|)(zsoltar|zsoltár)/i',$text,$match)) {
		$text = $match[3].$match[1];
	}
	
	/* összeszedjük a lehetséges könyv rövidítéseket */
	$query = "SELECT trans, abbrev, url FROM ".DBPREF."tdbook ORDER BY trans ";
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
	foreach($rs as $row) {
		if($row->url != '') {
			$books[$row->trans][] = preg_replace('/ /','',$row->url);
			$abbrevs2[preg_replace('/ /','',$row->abbrev)] = preg_replace('/ /','',$row->url);
            $abbrevs1[preg_replace('/ /','',$row->abbrev)] = preg_replace('/ /','',$row->abbrev);
			$abbrevs = array_merge($abbrevs1,$abbrevs1);
			
			$pattern = '/^'.preg_replace('/ /','',$row->url).'([0-9]{1,2}|$)/i';
			$text = preg_replace($pattern,preg_replace('/ /','',$row->abbrev).'$1',$text);
			
			$pattern = '/^'.preg_replace('/ /','',$row->abbrev).'([0-9]{1,2}|$)/i';
			$text = preg_replace($pattern,preg_replace('/ /','',$row->abbrev).'$1',$text);
			
			//$text = preg_replace('/Bolcs/','Bölcs',$text);
		}        
	 } 
	$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";	
	if(preg_match($pattern,$text,$matches))  {
	//echo $reftrans."<pre>"; print_R($books);

	
	$book = $matches[1];
		if($forcedtrans != false AND in_array($book,$books[(int) $forcedtrans])) {
			//$reftrans = $forcedtrans;
		}
		elseif(!in_array($book,$books[$reftrans])) {
			for($i=5;$i>0;$i--) {
				if(isset($books[$i]) AND is_array($books[$i]) AND $i != $reftrans AND in_array($book,$books[$i])) {
					//$reftrans = $i;
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
		
		
		if(is_array($tmps)) foreach($tmps as $tmp) {
		$select = "SELECT * FROM ".DBPREF."szinonimak WHERE tipus = 'konyv' AND (szinonimak LIKE '%|".preg_replace('/ /','',$tmp)."|%' OR  szinonimak LIKE  '%|".preg_replace('/ /','',$tmp).":%');";
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
		$pattern = "/^(".implode("|",$abbrevs).")(.*?)[a-f]{1}(.*?)/";
		//echo "<br>".$text;
		$text = preg_replace($pattern,'$1$2$3',$text);
		//echo "->".$text;
		//echo "<==".$pattern;
		
		$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";
		preg_match($pattern,$text,$matches);
        global $bookabbrevs;
				//echo $text;
				//print_R($abbrevs1);
				//echo $matches[1];
				
		$quote['book'] = $bookabbrevs[$reftrans][$matches[1]]['abbrev'];
		$quote['bookurl'] = $bookabbrevs[$reftrans][$matches[1]]['abbrev'];
		$quote['code'] = preg_replace("/^(".implode("|",$abbrevs).")/",'$1 ',$text);
		$quote['reftrans'] = $reftrans;
		$pattern = "/^(".implode("|",$abbrevs).")([0-9]{1,3})$/i";
		if(preg_match($pattern,$text,$match)) {
			$query = "SELECT numv FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans  WHERE ".DBPREF."tdverse.trans = ".$quote['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$quote['book']."' AND chapter = ".$match[2]." ORDER BY ABS(numv) DESC LIMIT 1";
			$numv = db_query($query);
			if(is_array($numv) AND $numv[0]['numv']>0) {
				/* TODO: vesszőt vagy kettőspontot?? */
				$quote['tag'][1]['code'] = $match[2].",1-".$numv[0]['numv'];
				$quote['tag'][1]['chapter'] = $match[2];
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
					if(count($tmp)>2) { //print_R($tmp);
					for($c=$tmp[1];$c<=$tmp[2];$c++) {
						$query = "SELECT numv FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".DBPREF."tdverse.trans = ".$quote['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$quote['book']."' AND chapter = $c ORDER BY ABS(numv) DESC LIMIT 1";
						$numv = db_query($query);
						if(is_array($numv) AND $numv[0]['numv']>0) {
							/* TODO: vesszőt vagy kettőspontot?? */
							$quote['tag'][$key*100+$c]['code'] = $c.",1-".$numv[0]['numv'];
							$quote['tag'][$key*100+$c]['chapter'] = $c;
							for($s=1;$s<=$numv[0]['numv'];$s++)
								$quote['tag'][$key*100+$c]['numv'][] = $s;							
						}
					} }
					break;
				case 2:
					preg_match('/^([0-9]{1,3})(,|:)([0-9]{1,2})-([0-9]{1,3})(,|:)([0-9]{1,2})$/',$tag,$tmp);
					for($c=$tmp[1];$c<=$tmp[4];$c++) {
						$query = "SELECT numv FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".DBPREF."tdverse.trans = ".$quote['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$quote['book']."' AND chapter = $c ORDER BY ABS(numv) DESC LIMIT 1";
						$numv = db_query($query);
						if(is_array($numv) AND $numv[0]['numv']>0) {
							if($c==$tmp[1]) $from = $tmp[3]; else $from = 1;
							if($c==$tmp[4]) $to = $tmp[6]; else $to = $numv[0]['numv'];
							$quote['tag'][$key*100+$c]['code'] = $c.$tmp[2].$from."-".$to;
							$quote['tag'][$key*100+$c]['chapter'] = $c;
							for($s=$from;$s<=$to;$s++)
								$quote['tag'][$key*100+$c]['numv'][] = $s;				
						}
					}
					break;
				case 1:
					preg_match('/^([0-9]{1,3})(,|:)(.*?)$/',$tag,$tmp);
					$query = "SELECT numv FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".DBPREF."tdverse.trans = ".$quote['reftrans']." AND ".DBPREF."tdbook.abbrev = '".$quote['book']."' AND chapter = ".$tmp[1]." ORDER BY ABS(numv) DESC LIMIT 1";
					$numv = db_query($query);
					if(is_array($numv) AND $numv[0]['numv']>0) {
						$quote['tag'][$key*100]['chapter'] = $tmp[1];
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
		//echo"<pre>".print_r($quote,1);
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

	//echo"<pre>"; print_R($verses);
	if(in_array('verses',$args)) {
		$averses = $verses;
		
		// TODO: fejezetváltásokkor
		$verses = print_verses($verses);
//		echo"<pre>".print_r($averses,1);		
		if($averses == array()) $error[] = 'Nincs megjeleníthető vers!';
		
		$tmpverses = array();
		$verses = '';
		foreach($averses as $v) {
			global $db;
			$query = "SELECT gepi, ".DBPREF."tdverse.trans, did, numv, gepi, tip, verse FROM ".DBPREF."tdverse WHERE gepi = ".$v['gepi']." AND trans = ".$v['trans'];
			$stmt = $db->prepare($query);
			$stmt->execute();
			$rs = $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_GROUP);	
			$rs =  array_shift(array_values($rs));
			$tmp = array();
			foreach($rs as $key => $jelenseg) {
					$tmp[$jelenseg->tip] = $jelenseg;
				}        
			$verse = $tmp;
			$verses .= showverse($tmp);
			$tmpverses[] = $tmp;
		}
	
		if(isset($pverses) AND $pverses == "<span class='alap'></span>	") { $return .= "Nincs találat.";}
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
			if(!isset($datatext)) $datatext = $description;
			
			$meta = '<meta property="og:description" content="'.$description.'">';
			global $texttosearch;
			
			$url = preg_replace('/ /','',preg_replace('/^('.$code['book'].')/',$code['bookurl'],$code['code']));
			$meta .= '<meta property="og:url" content="'.BASE.urlencode($url).'/" />';
			
			global $texttosearch;
			$meta .= '<meta property="og:title" content="Idézet a Szentírásból: '.$texttosearch.'">';
			
			global $share;
		
			$share .= '
				<div id="facebook">
					<a expr:share_url="data:post.url" href="http://www.facebook.com/sharer.php?" name="fb_share" rel="nofollow" type="button">Share</a>
					<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"/></script>
				</div>
				 ';
			
			$share .= '<div id="twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-related="jasoncosta" data-lang="hu"  data-count="none" data-hashtags="Biblia" data-url="'.BASE.urlencode($url).'/" data-text="'.$datatext.'">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
			$share .= '<input style="padding:2px;" type="button" onclick="window.prompt(\'Rövid cím, amin elérhető ez az oldal:\',\''.BASE.urlencode($url).'\');" value="rövid url">';
			//$share .= 'url: <a href="'.BASE.urlencode(preg_replace('/ /','',$texttosearch)).'">'.urlencode(preg_replace('/ /','',$texttosearch)).'</a>';
		}
	}
	$dids = array();
	global $db, $reftrans,$comments,$reftrans;
	foreach($averses as $verses) {
	if(isset($verses['did'])) {
		$query = "select * FROM ".DBPREF."tdverse where did = ".$verses['did']." order by did";
		$rs = db_query($query);
		list($list1,$list2,$list3)= listcomm($rs,$reftrans);
		$rs = $list1;
		foreach($rs as $row) {
			$dids[$row->did] = " did = '".$row->did."' ";
		}
	} else $return .= 'Mégsincs találat. Elnézést.'; }
	
	$query = "select * FROM ".DBPREF."tdcomm where (".implode(' OR ',$dids).") order by did";
	$rs = db_query($query);
	$comments .= showcomms($rs, $reftrans,100,100);	

	
	if(in_array('errors',$args) OR is_array($error)) {$return .="<br>".print_errors($error); }
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
				//echo $kod['reftrans']." ".$kod['book']." ".$tag['chapter'].":".$numv."<br>\n";
				$where = array(
					DBPREF.'tdverse.trans'=>$kod['reftrans'],
					DBPREF.'tdbook.abbrev'=>$kod['book'],
					'chapter'=>$tag['chapter'],
					'numv'=>$numv);
				$w = array();
				foreach($where as $name=>$value) $w[] = " ".$name." = '".$value."'";
				$query = "SELECT * FROM ".DBPREF."tdverse LEFT JOIN ".DBPREF."tdbook ON book = ".DBPREF."tdbook.id AND ".DBPREF."tdbook.trans = ".DBPREF."tdverse.trans WHERE ".implode(' AND ',$w)." LIMIT 1;";
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


/*
function add_verses($code,$start = false) {

	global $verses;
	global $book;
	global $reftrans;
	global $error;
	$tmp = explode(',',$code);
	$chapter = db_query("SELECT chapter FROM tdchapter as c, tdbook as b WHERE c.trans = b.trans AND c.abbook = b.abbrev AND b.did = $book AND numch = ".$tmp[0]." LIMIT 0,1");
	if(!is_array($chapter)) { $error[] = "Nincs is ennyi fejezete a könyvnek."; $chapter = $tmp[0]; //return;
	}
	else $chapter = $chapter[0]['chapter'];
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
*
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
/**/
function print_errors($error) {
    $return =  "<span class=\"alap\"><font color='red'>";
	foreach($error as $er) $return .= $er."<br>";
	$return .= "</font></span>";
	return $return;
}

function replace_hivatkozas($m) {
	global $books, $reftrans;
	foreach($books as $book) if($book['trans'] ==  $reftrans) $abbrevs[] = $book['abbrev'];	
	$verses = preg_replace('/ /','',$m[1]);
	$pattern = "/(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*/i";
	$verses = preg_replace_callback($pattern,"replace_hivatkozas2link",$verses);
	return $verses;
}
function replace_hivatkozas2link($m) {
	global $translations;
	$return = '';
	$quote = isquotetion($m[0]);
	//print_R($quore);
	if(is_array($quote)) {
		$return = "<a href='".BASE.$translations[$quote['reftrans']]['abbrev']."/".$quote['code']."' class='hivatkozas' style='/*font-size: 21px;*/color: #6274B5;'>[".$quote['code']."]</a>";
	} else $return = $m[0];
	return $return;

}

function print_verses($verses) {
	global $reftrans;
	

	$return = "<span class='alap'>"; 
	foreach($verses as $k=>$verse) {
		if($verse != '') {
		
			$verse['verse'] = preg_replace('/ "/',' „',$verse['verse']);
			$verse['verse'] = preg_replace('/"( |,|\.|$)/','”$1',$verse['verse']);
			
			//$pattern = "/{(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*}/i";
			$pattern = "/{(.*?)}/";
			$verse['verse'] = preg_replace_callback($pattern,'replace_hivatkozas',$verse['verse']);
		
			if($verse['title']!='') $return .= "<p class='kiscim'>".$verse['title']."</p>";
		if(array_key_exists('start',$verse) OR $k == 0 OR $numch != $verse['chapter']) $return .= " <strong>".$verse['chapter']."</strong> ";
		$return.= " <sup>".$verse['numv']."</sup>".$verse['verse']." ";
		$numch = $verse['chapter'];
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


function insert_stat($texttosearch, $reftrans, $results,$type = '') {
	global $tipps, $original, $translations;
	global $tracker;
			
	global $rows,$page;
	/*$tmp = array();
	for($i=(($page-1)*$rows)+1;$i<=$page*$rows;$i++) {
		$tmp[$i] = $results[$i];
	}
	$results = $tmp; */
	global $count; //$count = count($results);
	if($type == 'quote') $count =  $results;
	
	if(!is_array($results)) $results = array();
	
/* Assemble Page information *
global $event, $session, $visitor;
if($type == '') {
	$event->setCategory('Search');
	$event->setAction($translations[$reftrans]['abbrev']);
	$event->setLabel($texttosearch);
	$event->setValue($results);
} elseif($type == 'API') {
	$tmp = explode('|',$texttosearch);
	$tmp2 = explode(':',$tmp[0]);	
	$event->setCategory('API');
	$event->setAction($tmp2[1]);
	$event->setLabel($tmp[1]);
	$event->setValue($tmp[2]);
} elseif($type == 'rovid') {
	$event->setCategory('Rövidítés');
	$event->setAction($translations[$reftrans]['abbrev']);
	$event->setLabel($texttosearch);
	if($results == 0) $results = 1;
	$event->setValue($results);
}

*/

//	echo $event->getValue();
//$event = new GoogleAnalytics\Event('Search',$trans['abbrev'],$texttosearch,$results);
// Track page view
//$tracker->trackEvent($event, $session, $visitor);
	
	
	if(isset($_SERVER['HTTP_REFERER'])) $server = $_SERVER['HTTP_REFERER']; else $server = '';
	
	$tipp = strip_tags(implode('\n',$tipps));
	db_query("SET NAMES 'utf8'");
	db_query("SET CHARACTER SET 'utf8'");
	global $searchby;
	if($type == 'quote') $notes = 'searchby:quote';
	elseif($type == 'api') {
		global $apinotes;
		$notes = 'searchby:api'.$apinotes;
	}
	elseif($type == 'ebook') {
		$notes = 'type:'.$results['tipus'].'|uj:'.$results['uj'];
	}
	else $notes = 'searchby:'.$searchby.'|rows:'.$rows.'|page:'.$page;
	
	$query = "INSERT INTO ".DBPREF."stats_texttosearch 
		(texttosearch,notes,reftrans,date,result,session,tipp,original,referrer)
		VALUES ('".$texttosearch."','".$notes."',".$reftrans.",'".date('Y-m-d H:i:s')."',".$count.",'".session_id()."','".$tipp."','".$original."','".$server."');";
	db_query($query);
	if($type == 'quote') $stype = 'quote';
	elseif($type == 'api') $stype = 'api';
	elseif($type == 'ebook') $stype = 'ebook';
	else $stype = $searchby;
	
	$query = 
		"SELECT texttosearch, searchcount 
			FROM ".DBPREF."stats_search 
			WHERE 
				texttosearch = '".$texttosearch."' 
				AND reftrans = ".$reftrans." 
				AND rows = '".$rows."'
				AND page = '".$page."'
				AND searchtype = '".$stype."'
			ORDER BY texttosearch DESC LIMIT 1";
	$result = db_query($query,1);
	
	//echo '--'.$GLOBALS['fullsearch'].'--'.print_r($result,1);
	$searchcount = ($result[0]['searchcount']+1);
	if($searchcount > 1) $resultarray = $results;
	else $resultarray = array();
	
	if(is_array($result)) {	
		if($GLOBALS['fullsearch'] == 1) {} else {};
			$query = 
				"UPDATE ".DBPREF."stats_search 
					SET 
						searchcount = ".$searchcount." ,						
						resultarray = '".serialize($resultarray)."', 
						resultupdated = '".date('Y-m-d H:i:s')."',
						resultcount = ".$count."
					WHERE 
						texttosearch = '".$texttosearch."' 
						AND reftrans = ".$reftrans."
						AND rows = '".$rows."'
						AND page = '".$page."'
						AND searchtype = '".$stype."'						
						;";		
			db_query($query,1);
	} else {
		if($GLOBALS['fullsearch'] == 1) {} else {};
			$query =
				"INSERT INTO ".DBPREF."stats_search 
					(texttosearch,reftrans,searchcount,resultcount,resultarray,resultupdated,rows,page,searchtype) 
				VALUES ('".$texttosearch."',".$reftrans.",1,".$count.",'".serialize($resultarray)."','".date('Y-m-d H:i:s')."','".$rows."','".$page."','".$stype."');";
			db_query($query);
	}
  }
?>
