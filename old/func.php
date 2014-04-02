<?php
/**
 * Egy szövegből kisbetűs csak betűket és szünetet tartalmazó szöveget gyárt.
 * @param string $verse Bejövő szöveg. Általban szentírási vers.
 * @return sring Egyszerűsített szöveg.
 */
function simpleverse($verse) {
    $verse = strip_tags($verse);
	$verse = preg_replace('/([^a-zA-zöőóúüűáéíÖ ŐÓÚÜŰÁÉÍ]*)/is','',$verse);
	$verse = preg_replace('/( ){2,10}/is',' ',$verse);
    $verse = strtolower($verse);
	return $verse;
}

/**
 * Egy szövegben megpróbálja az összes szót kicserélni a szótövére
 * @param string $verse Bejövő szöveg
 * @return string Kisbetűs egyszerű szöveg lehetőleg szótövekkel
 */
function rootverse($verse) {
    $return = ''; $output = array();
    exec('echo "'.$verse.'" | hunspell -d hu_HU -s -i UTF-8',$output);
    
    /* TODO: finomítandó, mert még mindig a legrosszabbat eszi meg (második ajánlat) */
    foreach($output as $line) {
        if($line == '' and isset($szo)) {
          $tmp = explode(' ',$szo);
          if(isset($tmp[1])) $return .= ' '.$tmp[1];
          else $return .= ' '.$tmp[0];          
        }
        $szo = $line;     
    }
    $return = strtolower(strip_tags($return));
    return trim($return);

}

/**
 * Egy fordításban rákeres egy szóra vagy kifejezésre
 * Megnézi, hogy van-e pontos egyezés, vagy szóeleji-szóvégi vagy a 
 * **simpleverse** vagy a **rootverse** függvényekkel ugyan ez.
 * @param string $text Kereső szöveg, akár filterrel. pl. "egér in:Újszöv"
 * @param integer $reftrans A használni kívánt fordítás kódja.
 * @param string $min A minimálisan elérendő találatszám, amíg küzd.
 * @return array [<br/>
 * 				'{verse-id}' 		=> egy-egy vers és mindenféle adata<br/>
 * 			]<br/>
 */
function search($text,$reftrans, $min) {
	//TODO: " in:Újszöv "
	global $bookabbrevs;
		if(preg_match('/ in:([^ ]*)$/',$text,$match)) {
			$text = preg_replace('/(.*) in:'.$match[1].'$/','$1',$text);
			$in = $match[1];	
		
			if(preg_match("/^(Újszöv|Új|Újszövetség|Ótestamentum|Ótestámentum)$/i",$in)) $inwhere = " AND gepi LIKE '2%' ";
			elseif(preg_match("/^(Ó|Ószöv|Ószövetség|Újtestamentum|Újtestámentum)$/i",$in)) $inwhere = " AND gepi LIKE '2%' ";
			elseif(preg_match("/^(Lk)$/i",$in,$m)) $inwhere = "AND gepi LIKE '".$bookabbrevs[$reftrans][$m[1]]['id']."%' ";
		}
		
  $results = array();

   $reward = array(
	'cs_nonwrapped_verse' => 40000,
	'cs_nonwrapped_versesimple' => 6000,
	'cs_nonwrapped_verseroot' => 5000,	
	'cs_wrapped_verse' => 500,
	'cs_wrapped_versesimple' => 400,
	'cs_wrapped_verseroot' => 100,
	'cis_nonwrapped_verse' => 20000,
	'cis_nonwrapped_versesimple' => 4000,
	'cis_nonwrapped_verseroot' => 3000,	
	'cis_wrapped_verse' => 500,
	'cis_wrapped_versesimple' => 400,
	'cis_wrapped_verseroot' => 100,
   );
   
   /* egyben */
   $text_verse = $text;
   $text_versesimple = simpleverse($text);
   $text_verseroot = rootverse($text);
   
   $where = " (verse LIKE '%".$text_verse."%' OR versesimple LIKE '%".$text_versesimple."%' OR verseroot LIKE '%".$text_verseroot."%')  ";
   if(isset($inwhere)) $where .= $inwhere;
   $mysqlrows = dbsearchtext($where,$reftrans);
   foreach($mysqlrows as $row) {
		foreach(array('cs','cis') as $csens) { //CaseSensitive or CaseInSensitive
			foreach(array('wrapped','nonwrapped') as $wrap) {
				foreach(array('verse','versesimple','verseroot') as $cell) {
					if($wrap == 'nonwrapped') $pattern = '/( |[ ,\"\'\-„]{1}|^)('.${'text_'.$cell}.')( |[ ,\"\'„\-;?!”.]{1}|$)/us';
					else $pattern = '/('.${'text_'.$cell}.')/us';
					if($csens == 'cis') $pattern .= 'i';
					if(preg_match_all($pattern,$row[$cell],$matches,PREG_SET_ORDER)) {
						$num = (count($matches) * $reward[$csens."_".$wrap."_".$cell]);
						//echo $csens."-".$wrap."-".$cell.": ".count($matches)."=> ".$num."\n";
						$results = addresults(array($row['gepi']=>$row),$results,$num);
					}
				}
			}
		}
   }
   if(count($results) > $min * 1.1 ) return resultsorder($results);
  
  /* darabokban, egy versen belül */
  $segments = array(); $texttmp = $text;
  preg_match_all('/"(.*?)"/',$text,$matches,PREG_SET_ORDER);
  foreach($matches as $match) {
	$segments[] = $match[1];
	$texttmp = preg_replace('/'.$match[0].'/','',$texttmp);
  }
  $segments = array_merge(explode(' ',$texttmp),$segments);
  foreach($segments as $k=>$v) if($v == '') unset($segments[$k]);

  $where = array(); $segments2 = array();
  foreach($segments as $key => $segment) {
	$segments2[$key]['text_verse'] = $segment;
	$segments2[$key]['text_versesimple'] = simpleverse($segment);
	$segments2[$key]['text_verseroot'] = rootverse($segment);
   $where['verse'][] = " verse LIKE '%".$segments2[$key]['text_verse']."%' ";
   $where['versesimple'][] = " versesimple LIKE '%".$segments2[$key]['text_versesimple']."%' ";
   $where['verseroot'][] = " verseroot LIKE '%".$segments2[$key]['text_verseroot']."%' ";
   }
  
  $query = "( (".implode(' AND ',$where['verse']).") OR (".implode(' AND ',$where['versesimple']).") OR (".implode(' AND ',$where['verseroot']).") )";
  if(isset($inwhere)) $query .= $inwhere;
  $mysqlrows = dbsearchtext($query,$reftrans);   
 
   foreach($mysqlrows as $row) {
		foreach(array('cs','cis') as $csens) { //CaseSensitive or CaseInSensitive
			foreach(array('wrapped','nonwrapped') as $wrap) {
				foreach(array('verse','versesimple','verseroot') as $cell) {
					$ok = 0;
					foreach($segments2 as $segment) {
						if($wrap == 'nonwrapped') $pattern = '/( |[ ,\"\'\-„]{1}|^)('.$segment['text_'.$cell].')( |[ ,\"\'„\-;?!”.]{1}|$)/us';
						else $pattern = '/('.$segment['text_'.$cell].')/us';
						if($csens == 'cis') $pattern .= 'i';
						if(preg_match_all($pattern,$row[$cell],$matches,PREG_SET_ORDER)) {
							$ok++;
							//$num = (count($matches) * $reward[$csens."_".$wrap."_".$cell]) / count($segments);
							//echo $csens."-".$wrap."-".$cell.": ".count($matches)."=> ".$num."\n";
							//$results = addresults(array($row['gepi']=>$row),$results,$num);
						}
					}
					if($ok == count($segments2)) {
							$num = ($reward[$csens."_".$wrap."_".$cell]) / ( count($segments2) * 2 );
							$results = addresults(array($row['gepi']=>$row),$results,$num);
					}
				}
			}
		}
   }
   if(isset($in)) $text .= ' in:'.$in;
   if(count($results) > $min * 1.1 ) return resultsorder($results);
   /* darabokan vége */
   $GLOBALS['fullsearch'] = 1;
  //exit;		
   return resultsorder($results);
}

/**
 * Keresési eredmények többdimenziós tömbjét rendezi sorba az alapján, hogy
 * mennyi pontot kapott egy-egy vers.
 * @param array $results Egy hivatkozás, pl. 1Kor 13, 1-13
 * @return array
 */
function resultsorder($results) {
		$order1 = array(); $order2 = array();
	  foreach($results as $key=>$res) {
				$order1[$key] = $res['point'];
				$order2[$key] = $key;
				ksort($results[$key]['verses']);				                
	 }
	  array_multisort($order1,SORT_DESC, $order2, SORT_ASC, $results);
	  
	  //echo"<pre>".print_R($results,1);
	  return $results;
}

/**
 * Keresési eredmények nagy tömbjéhez hozzáilleszt még egyet. Ha már megvan, 
 * akkor csak a pontszámát növeli.
 * @param array $news Új eredmények. Egyetlen tömbben több eredmény is érkezhet
 * @param array $old Az eddigi erdmények
 * @param integer $num Mennyi pontot kapjon ezért a találatért. A súlyozáshoz kell.
 * @return array Az eredmények multitömbje.
 */
function addresults($news,$old,$num = false) {
	if($num == false) $num = 1;
	//if(count($news)>0) echo $num."+";
	foreach($news as $gepi => $new) {
		
		global $searchby;
		if($searchby == 'byverse') $key = $gepi;		
		else $key = substr($gepi, 0, 6);	
	
		if(array_key_exists($key,$old)) {
			$old[$key]['point'] = $old[$key]['point'] + $num;
			
			if(!isset($old[$key]['verses'][$gepi])) {
				$old[$key]['verses'][$gepi] = $new;
			}
			
		} else {			
			$old[$key]['point'] = $num;
			$old[$key]['verses'][$gepi] = $new;
		}
		
	}
	return $old;
}

function searchsimple($name,$text,$reftrans) {
		$tmp = array();
	switch ($name) {
		/* alap - case sensitive */
		case 'verse_ahogyvan': //( |[ ,\"\'„-;?!”.]|$)
			$tmp = dbsearchtext("verse regexp '( |[ ,\"\'„]|^)".$text."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);	
			break;
		case 'versesimple_ahogyvan':
			$tmp = dbsearchtext("versesimple regexp '( |[ ,\"\'„]|^)".simpleverse($text)."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);			
			break;
		case 'verseroot_ahogyvan':
			$tmp = dbsearchtext("verseroot regexp '( |[ ,\"\'„]|^)".rootverse($text)."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);			
			break;			
		case 'verse_beolvadva':
			$tmp = dbsearchtext("verse regexp '".$text."' ",$reftrans);			
			break;
		case 'versesimple_beolvadva':
			$tmp = dbsearchtext("versesimple regexp '".simpleverse($text)."' ",$reftrans);			
			break;
		case 'verseroot_beolvadva':
			$tmp = dbsearchtext("verseroot regexp '".rootverse($text)."' ",$reftrans);			
			break;
		
		/* case insensitive */
		case 'verse_ahogyvan_kicsi': //( |[ ,\"\'„-;?!”.]|$)
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(verse) regexp '( |[ ,\"\'„]|^)".$text."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);	
			break;
		case 'versesimple_ahogyvan_kicsi':
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(versesimple) regexp '( |[ ,\"\'„]|^)".simpleverse($text)."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);			
			break;
		case 'verseroot_ahogyvan_kicsi':
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(verseroot) regexp '( |[ ,\"\'„]|^)".rootverse($text)."( |[ ,\"\'„-;?!”.]|$)' ",$reftrans);			
			break;			
		case 'verse_beolvadva_kicsi':
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(verse) regexp '".$text."' ",$reftrans);			
			break;
		case 'versesimple_beolvadva_kicsi':
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(versesimple) regexp '".simpleverse($text)."' ",$reftrans);			
			break;
		case 'verseroot_beolvadva':
			$text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
			$tmp = dbsearchtext("LOWER(verseroot) regexp '".rootverse($text)."' ",$reftrans);			
			break;
	}
	return $tmp;

}

/**
 * Rákeres egy kifejezésre a mysql adatbázisban
 * @param string $query A kereső kifejezés.
 * @param string $reftrans A használni kívánt fordítás kódja.
 * @return array Az egyes versek gépikódja a kulcs.
 */
function dbsearchtext($query,$reftrans) {
	$return = array();
	$query = "select gepi, verse, versesimple, verseroot from ".DBPREF."tdverse  where (".$query.") and ".DBPREF."tdverse.trans=$reftrans";
	global $isinbook;
	if($isinbook != '') $query .= ' '.$isinbook;
    //echo $query."<br/>";
	$results = db_query($query);
	if(is_array($results)) 
		foreach($results as $r) {
			$return[$r['gepi']] = $r;
		}
	return $return;

}

/**
 * Összeszedi a híreket, ha vannak. Általában a főoldalon.
 * @return array Egyesével a címoldali hírek.
 */
function getnews() {
	global $scripts;
	$scripts[] = 'news.js';
	$query = "select * from ".DBPREF."news where frontpage = 1 order by date DESC";
	global $db;
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
	$return = array();
	foreach($rs as $r) {
		$return[] = array($r->title,$r->text);
	}
	return $return;
}

class Menu {

    var $items;  // Items in our shopping cart

    function add_item($title, $url) {
        $this->items[] = array('url'=>$url,'title'=>$title);
    }

	function add_pause() {
        $this->items[] = 'pause';
    }
	
	function add_text($text) {
        $this->items[] = $text;
    }
	
    // Take $num articles of $artnr out of the cart

    function remove_item($artnr, $num) {
        if ($this->items[$artnr] > $num) {
            $this->items[$artnr] -= $num;
            return true;
        } elseif ($this->items[$artnr] == $num) {
            unset($this->items[$artnr]);
            return true;
        } else {
            return false;
        }
    }
	
	function html() {
		echo"
		<table border='0' cellpadding='0' cellspacing='0' width='750'>";
		if(isset($this->items)) {
		foreach($this->items as $item) {	
		if($item=='pause') {
			echo"<tr><td style='height:15px'></tr>";
		}
		elseif(!is_array($item))  {
			/*echo"<tr><td style='background-color:#DD3C5B;padding:5px;padding-left:40px'>".$item."</tr>";*/
            echo"<tr><td class='menu' >".$item."</tr>";
		}
		else {
		echo"<tr>
            <td style='height:3px'></tr>
			<tr>
            <td background='../img/vmenucolorbg.gif' width='140' align='left' class='menu'>";		  
					  
		echo '<a href="'.url($item['url']).'" class="menulink">'.$item['title'].'</a>';
        
		echo"
                  </td>
                </tr>";
			}
		}}
		echo "</table>";
	
	}
}

//TODO: Ezt itten kiírtani. Gyanúsan hülyeség.
/**
 * URL-eket rendez
 * Ha külsőre mutat, akkor nem bántja. Egyébként hosszúvá teszi
 */
function url($url) {
	if(!preg_match('/(http:\/\/|^\/)/i',$url)) {
		$tmp = explode('?',$url);
		if(isset($tmp[1])) {
			$vars = explode('&',$tmp[1]);
			foreach($vars as $key => $var) {
				if(preg_match('/^q=/i',$var)) unset($vars[$key]);
			}
			$tmp[1] = implode('&',$vars);
		}
		$newurl = BASE.'index.php?q='.$tmp[0];
		if(isset($tmp[1])) $newurl .= '&'.$tmp[1];
	} else $newurl = $url;

	return $newurl; 
}

/**
 * Ha hosszú url-el hívták meg az oldalt, akkor átirányít az elegáns rövid
 * megfelelőjére általában Error 301-el
 *
 * Egy jó részére azért van szükség, mert régi honlapok még a régi hosszú 
 * url-ekre mutatnak. De valószínűleg a keresésnél is kell, mert a kereső form
 * csak hosszúra küld.
 * 
 */
function redirect_long2short() {
	if(isset($_REQUEST['searchby'])) $_SESSION['searchby'] = $_REQUEST['searchby'];
   /* 
    * A hosszú url-ből kitalája, hogy mi lenne röviden
	* és oda átirányítja
	*/
	global $translations;
	
	if(!isset($_REQUEST['q'])) return;
 
if($_REQUEST['q'] == 'showtrans') {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
			if($tdtrans['id'] == $reftrans) {
				Header( "HTTP/1.1 301 Moved Permanently" ); 
				Header( "Location: ".BASE.$tdtrans['abbrev']); 
				exit;
			}
	}
} elseif($_REQUEST['q'] == 'showbook' AND isset($_REQUEST['abbook']) ) {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
			if($tdtrans['id'] == $reftrans) {
				foreach($books as $book) {
					if($book['abbrev'] == $_REQUEST['abbook'] AND $tdtrans['id'] == $book['trans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".BASE.$tdtrans['abbrev'].'/'.$_REQUEST['abbook']); 
						exit;
					}
				}
			}
	}
} elseif($_REQUEST['q'] == 'showchapter' AND isset($_REQUEST['abbook']) AND isset($_REQUEST['numch'])) {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
		//echo mb_detect_encoding($_REQUEST['abbook'],'UTF-8','ISO-8859-2');
		//echo iconv('ISO-8859-2','UTF-8',urldecode($_REQUEST['abbook']));
		
			if($tdtrans['id'] == $reftrans) {
				foreach($books as $book) {
					if($book['abbrev'] == $_REQUEST['abbook'] AND $tdtrans['id'] == $book['trans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".BASE.$tdtrans['abbrev'].'/'.$_REQUEST['abbook'].$_REQUEST['numch']); 
						exit;
					}
				}
				foreach($books as $book) {
					if($book['abbrev'] == iconv('ISO-8859-2','UTF-8',$_REQUEST['abbook']) AND $tdtrans['id'] == $book['trans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".BASE.$tdtrans['abbrev'].'/'.iconv('ISO-8859-2','UTF-8',$_REQUEST['abbook']).$_REQUEST['numch']); 
						exit;
					}
				}
			}
	}
} elseif($_REQUEST['q'] == 'searchbible' AND isset($_REQUEST['texttosearch']) AND isset($_REQUEST['reftrans'])) {
    
        $texttosearch = $_REQUEST['texttosearch'];
        $reftrans = (int) $_REQUEST['reftrans'];
        $code = isquotetion($texttosearch,$reftrans);

	if($code)  {
		
		Header( "HTTP/1.1 301 Moved Permanently" ); 
		Header( "Location: ".BASE.$translations[$_REQUEST['reftrans']]['abbrev'].'/'.preg_replace('/ /','',$code['code']));
		exit;
	} else {
		$extra = '';
		if(isset($_REQUEST['offset'])) $extra .= '&offset='.$_REQUEST['offset'];
		if(isset($_REQUEST['rows'])) $extra .= '&rows='.$_REQUEST['rows'];
		
		if(isset($_REQUEST['searchby'])) {
			if($_REQUEST['searchby'] == 'byverse' )
					$extra .= '&by=verse';
		}
		
		if(isset($_REQUEST['in']) AND $_REQUEST['in'] != '') $texttosearch .= ' in:'.$_REQUEST['in'];
		
		Header( "HTTP/1.1 301 Moved Permanently" ); 
		Header( "Location: ".BASE.$translations[$_REQUEST['reftrans']]['abbrev'].'/'.$texttosearch.$extra);
		exit;
	
	}
}  elseif($_REQUEST['q'] == 'searchbible') {
	Header( "HTTP/1.1 301 Moved Permanently" ); 
	Header( "Location: ".BASE.'kereses');
	exit;
}  elseif($_REQUEST['q'] == 'info') {
	Header( "HTTP/1.1 301 Moved Permanently" ); 
	Header( "Location: ".BASE.'info');	
	exit;
	
	
} elseif($_REQUEST['q'] == 'showallbible') {
    Header( "HTTP/1.1 301 Moved Permanently" ); 
	Header( "Location: ".BASE.'forditasok');
	exit;
} elseif(isset($_REQUEST['q'])) {
	$q = $_REQUEST['q'];
} else {
	//Header( "HTTP/1.1 301 Moved Permanently" ); 
	Header( "Location: ".BASE);
	exit;
}

}

/**
 * Feldolgoz egy urlt és megtalálja a szükséges változókat belőle. Nem valid
 * címeket pedig eldob.
 * @return array A kulcsból lesz változónév az értékből pedig változó.
 */
function url_short2vars() {
	/*
	* $_REQUEST['rewrite']-ból 
	* értelmes változókat gyárt
	*/
	global $translations, $books;
	
	global $reftrans; // kell-e?
	

	if(!isset($_REQUEST['rewrite']) OR $_REQUEST['rewrite'] == '') return array();
 
	//echo utf8_decode($_REQUEST['rewrite']);
	$teszt = iconv('UTF-8', 'UTF-8//IGNORE', utf8_decode($_REQUEST['rewrite']));
	if($teszt != '') {
		$_REQUEST['rewrite'] = $teszt;
	}
	
	/*
	// TODO: Talán átirányíthatna a hülyén kapja meg
	if(mb_check_encoding( utf8_decode($_REQUEST['rewrite']),'UTF-8')) {
		//$_REQUEST['rewrite'] = utf8_decode($_REQUEST['rewrite']);
		//$_REQUEST['rewrite'] = iconv('ISO-8859-2','UTF-8',utf8_decode($_REQUEST['rewrite']));		
	} else {

	}
	echo '-'.urldecode($_REQUEST['rewrite']);
	//echo $string = mb_convert_encoding($_REQUEST['rewrite'],'HTML-ENTITIES','utf-8');

	if (preg_match('!!u', $string))
{
echo "utf8";
   // this is utf-8
}
else 
{
echo "not utf8";
   // definitely not utf-8
}
 */
	
	$uri = explode('/',rtrim($_REQUEST['rewrite'],'/'));
   
	if($uri[0] == 'API') {
		$q = 'api';
		if(isset($uri[1])) $api = $uri[1]; else $api = '';
	} elseif(count($uri)==1) {
	
		if($uri[0] == 'forditasok')  		$q = 'showallbible';
		elseif($uri[0] == 'kereses')  		$q = 'searchbible';		
		elseif($uri[0] == 'API')  			$q = 'api';		
		elseif($uri[0] == 'info')  			$q = 'info';

		$istransid = array_search($uri[0], $GLOBALS['tdtrans_abbrev']);
		if($istransid != false) {
			$transid = $istransid;
			$q = 'showtrans';
		}
		
	} elseif(count($uri) == 2) {
		/* megnézzük, hogy az első fordítás-e */
		$istransid = array_search($uri[0], $GLOBALS['tdtrans_abbrev']);
		if($istransid != false) $transid = $istransid;
		else return array('q'=>'404');
		
		$isbookid = array_search($uri[1], $GLOBALS['tdbook_abbrev'][$transid]);		
		/* ha másodk epub/mobi */
		if($uri[1] == 'epub' OR $uri[1] == 'mobi') {
			$q = 'ebook';
			$type = $uri[1];
				
		/* ha a második könyv */		
		} elseif($isbookid != false) {
			$bookid = $isbookid;
			$q = 'showbook';
		}
		
		else {		
		/* Ha ékezetmentes véletlen */
		// nem egészen értem, no
		$t = $transid;	global $bookurls, $transid;	$transid = $t;
		$patternurl = '/^('.implode('|',$GLOBALS['tdbook_url'][$transid]).')([0-9]{1,3})/i';
		$uri[1] = preg_replace_callback(
				$patternurl,
				function ($matches) {
					global $bookurls, $reftrans, $transid;
					$abbrev = $bookurls[$transid][$matches[1]]['abbrev'];
					return $abbrev.$matches[2];

				},
				$uri[1]);
		
		/* ha a második fejezet */
		$pattern1 = '/^('.implode('|',$GLOBALS['tdbook_abbrev'][$transid]).')([0-9]{1,3})$/i';
		$pattern2 = "/^(".implode("|",$GLOBALS['tdbook_abbrev'][$transid]).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";	
		if(preg_match($pattern1,$uri[1],$match)) {
			$bookid = array_search($match[1], $GLOBALS['tdbook_abbrev'][$transid]);
			$chapter = $match[2];	
		    $q = 'showchapter';
		
		}
		elseif(preg_match($pattern2,$uri[1],$match)) {
	    /* ha a második idézet */
			$q = 'quotes';
			$text = $uri[1];
		} else {
		
		/* ha második keresés */
			$q = 'searchbible';
			$text = $uri[1];
		}
		
		}
	} elseif(count($uri) == 3) {
		
		$istransid = array_search($uri[0], $GLOBALS['tdtrans_abbrev']);
		if($istransid != false) $transid = $istransid;
		else return array('q'=>'404');
		
		$isbookid = array_search($uri[1], $GLOBALS['tdbook_abbrev'][$transid]);
		if($isbookid != false) $bookid = $isbookid;
		else return array('q'=>'404');
		
		if($uri[2] == 'epub' OR $uri[2] == 'mobi') {
			$q = 'ebook';
			$type = $uri[2];
			$isbookid = array_search($uri[1], $GLOBALS['tdbook_abbrev'][$transid]);
			if($isbookid != false) $bookid = $isbookid;
			else return array('q'=>'404');
		
		} elseif(is_numeric($uri[2])) {
		
			Header( "HTTP/1.1 301 Moved Permanently" ); 
			Header( "Location: ".BASE.$uri[0].'/'.$uri[1].$uri[2]); 
			exit;
			
		}					
	}
	$return = array();
	$varnames = array('q','text','api','type','transid','bookid','chapter');
	foreach($varnames as $name)
		if(isset($$name)) $return[$name] = $$name;
	return $return;
}

/**
 * Változót ment/frissít adatbázisba
 * @param string $name A változó neve.
 * @param string $value A változó értéke.
 */
function setvar($name,$value) {
	$test = getvar($name);
	if( $test == false) {
		$query="INSERT INTO ".DBPREF."vars (name, value) VALUES ('$name','$value')";
	} else {	
		$query='UPDATE '.DBPREF.'vars SET value = \''.$value.'\' WHERE name = \''.$name.'\'';
	}
	db_query($query);
}

/**
 * Változó értékét szerzi meg az adatbázisból
 * @param string $name A változó neve
 * @return string A változó értéke
 */
function getvar($name) {
	$query="SELECT * FROM ".DBPREF."vars WHERE name = '".$name."' LIMIT 0,1";
	$result = db_query($query);
	
	if(!$result) return false; 
	return $result[0]['value'];
}


/* Ezek a függvények az index.php végefeléhez tartoznak, amikoris a szövegben maradt helytelen urleket tisztázza */
	function url_showtrans($m) {
			global $translationIDs;
			return $m[1].BASE.$translationIDs[$m[2]]['abbrev'].$m[1];
		}
	function url_showbook($m) {
			global $translationIDs,$bookabbrevs;
			return $m[1].BASE.$translationIDs[$m[2]]['abbrev']."/".$bookabbrevs[$m[2]][$m[3]]['abbrev'].$m[1];
		}
	function url_showchapter($m) {
			global $translationIDs,$bookabbrevs;
			return $m[1].BASE.$translationIDs[$m[2]]['abbrev']."/".$bookabbrevs[$m[2]][$m[3]]['abbrev'].$m[4].$m[1];
		}
	function url_searchbible($m) {
			//print_R($m);
			global $translationIDs;
			if(!isset($m[5])) $m[5] = '';
			return $m[1].BASE.$translationIDs[$m[3]]['abbrev']."/".$m[2].$m[4].$m[5].$m[1];
		}
/**/

/*
 * SEARCHBIBLE segédfüggvényei
 *
 *
 */

 //TODO: Törölhető??
 //TODO: Itt is itt van a nagy központi kereső pattern. Csak nem friss.
/**
 * Egy idézet alternatíváit keresi. És egyből átirányít oda.
 * @param string $texttosearch Egy idézet kódja.
 */ 
function getIdezetTipp($texttosearch) {
	global $reftrans, $tipps;
	foreach($GLOBALS['tdtrans'] as $trans) {
		foreach($GLOBALS['tdbook'][$trans['id']] as $book) {
			$pattern = "/^(".implode("|",array($book['abbrev'])).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*$/i";	
			if(preg_match($pattern,$texttosearch,$matches))  {
				if(isset($matches[1])) {
						$jokonyv = $GLOBALS['tdbook'][$reftrans][$book['id']]['abbrev'];
						$jotext = preg_replace('/^'.$matches[1].'(.*?)$/',$jokonyv.'$1',$texttosearch);
						$tipps[] = 'Ebben a fordításban inkább így használd: <a href="'.BASE.$GLOBALS['tdtrans_abbrev'][$reftrans]."/".$jotext.'">'.$jotext.'</a> !';
						
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".BASE.$GLOBALS['tdtrans_abbrev'][$reftrans]."/".$jotext); 
						exit;
						
						return;
				
				}
			}
		}
	}
}

//TODO: global $reftrans; kiírtása
/**
 * Szinonímákat keres egy szóhoz ill. a fordításnak megfelelő szót/nevet.
 * @param string $texttosearch A keresett szó
 * @param integer $reftrans A fordítás azonosítója
 * @param integer $max Ennyi szinonímánál megáll.
 * @return array Egy-egy szinoníma szót tartalmaz.
 */
function getSzinonima($texttosearch,$reftrans,$max = 2) {
	$szinonima = array();	

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
					$szinonima[] = $s[0];
			}
		}
	}
	
	
	return $szinonima;
  }

 //TODO: global $tipps kiírtása?
 /**
 * Szinoníma tippeket ad egy keresésre
 * Megkeresi a szinonímákat, majd megnézni, hogy kerestek-e már arra.
 * @param string $texttosearch A keresett szó
 * @param integer $reftrans A fordítás azonosítója
 * Vissza nem tér, csak a global $tipps-be pakolgatja.
 */
function getSzinonimaTipp($texttosearch,$reftrans) {
	 
	 
	 $valtozatok = array();
	 //TODO: jó ez?
	 preg_match_all('/(^| |")([^ "]{1,100})/',$texttosearch,$matches,PREG_SET_ORDER);
	 foreach($matches as $match) {
		$szo = $match[2];
		$szinonimak = getSzinonima($szo,$reftrans);
		foreach($szinonimak as $szinonima) {
			$valtozatok[] = preg_replace('/(^| |")('.$szo.')( |"|$)/','$1'.$szinonima.'$3',$texttosearch);
		}
	}
	$valtozatokhtml = array();
	foreach($valtozatok as $valtozat) {
		$valt = '';
	
		global $searchby;
		$query = "
			SELECT resultcount 
			FROM ".DBPREF."stats_search
			WHERE
				searchtype = '".$searchby."'
				AND reftrans = '".$reftrans."'
				AND texttosearch = '".$valtozat."'
			ORDER BY resultcount DESC
			LIMIT 1";
		$result = db_query($query);	
		//echo $query."<br>";
		if(isset($result[0]) AND $result[0]['resultcount'] > 0) {
			$extra = '<sup>('.$result[0]['resultcount'].')</sup>';
		} else 
			$extra = '';
			
		if((isset($result[0]) AND $result[0]['resultcount'] > 0) OR !isset($result[0])) {		
				$valtozatokhtml[] = " <a href='".BASE.$GLOBALS['tdtrans_abbrev'][$reftrans]."/".urlencode($valtozat)."' class=link>".$valtozat.$extra."</a>";		
			}
	
	}
	if($valtozatokhtml != array()) {
			$return = "Talán próbáld más szavakkal: ";
			$tmp = array_chunk($valtozatokhtml, 5, true);
			$return .= implode(', ',$tmp[0]);
			$return .= '!';
			global $tipps; $tipps[] = $return;
	}
				
  }

/**
 * A Kereső teljes nagy Form-ot írja ki ajánlatokkal és tippekkel
 * Csak a searchbible.php használja
 * @return string HTML formázott szöveg, rögtön képernyőre írható
 */  
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
			<p class="kiscim">A keresőről:</p><br/>
			<p>A <strong>Szentírás szövegében lehet keresni</strong> vagy <strong>konkrét versre, versekre lehet hivatkozni</strong>.</p>
			<p>Az igehelyekre a „<i>Könyv</i> <i>fejezetszám</i>, <i>verszám</i>” formával lehet. Például: <a href="" class='link'>1Kor 13,1-7.9-11</a>. (Tehát a részt a verstől elválasztó kettőspontot nem tudja értelmezni a rendszer.) Figyeljünk arra, hogy a fordításnak megfelelő könyvrövidítést használjuk!</p>
			<p>A szövegrészleteket a <strong>súlyozva</strong> jeleníti meg. <strong>Fejezetenként vagy versenként csoportosítva</strong>.</p>
			<p>Lehetőség van arra, hogy <strong>egy-egy könyvre szűkítsük</strong> le a keresést vagy a teljes Ó- ill. Újszövetségre. Ehhez a kereső kifejezés végére(!) kell írni például azt, hogy „ in:Lk”</p>
			<p><strong>Idézőjelek</strong> segítségével lehet kötelezni a keresőt, hogy adott kifejezéseket együtt kezeljen.</p>
			<p>A kereső igyekszik szinonímákat és alternatívákt is ajánlani, ill. zárójeles felső indexbe megjeleníteni az adott változatban/fordításban várható találatok számát. Valamint a találatokat a gyorsabb újrakeresés érdekében tároljuk. Ha nem talál a pontos kifejezésnek megfelelőt, akkor megpróbál a keresőszó szótöve alapján újabb találatokat előásni.</p>
		
EOD;
		
		//'<div id="tipp"><font color="red">TIPP:</font> a keresét le lehet szűkíteni egy-egy könyvre vagy az Ószövetségre/Újszövetségre a keresés végére írt <strong>in:<i>könyvrövidítés</i></strong> formával! Például: <A href="http://szentiras.hu/SZIT/%C3%B6r%C3%B6m%20in:Lk">öröm in:Lk</a></div>';
		
		return $return;
	}

/**
 * A kereső találati oldal tetején a magyarázó és új kereső formot szedi össze
 * Csak a searchbible.php használja
 * @return string HTML formázott kész szöveg
 */
function print_form() {
		global $code;
		global $reftrans;
		global $query;
			
		global $base;
		$return = '<form name="input" action="'.$_SERVER['PHP_SELF'].'" method="get">
			<input type="text" name="quotation" value="'.$query.'" /><br />';
			
			$reftranss = db_query("SELECT * FROM ".DBPREF."tdtrans");
			
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

/**
 * Egy filet kínál fel letöltésre (gyakorlatilag kiírja)
 * Az ebook.php használja
 * @param string $filename A fájl neve
 * @param string $path A fájl elérhetősége
 */
function getdownload($filename,$path = '') {
	if($path == '') $path = '/var/www/szentiras.hu/ebook/';
	
	header("Content-Disposition: attachment; filename=" . urlencode($filename));    
	header("Content-Type: application/force-download");
	header("Content-Type: application/download");
	$fp = fopen($path.$filename, "r"); 
	while (!feof($fp))
	{
		echo fread($fp, 65536); 
		flush(); // this is essential for large downloads
	}  
	fclose($fp); 
}
 
?>
