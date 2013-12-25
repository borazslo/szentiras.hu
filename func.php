<?php
function simpleverse($verse) {
    $verse = strip_tags($verse);
	$verse = preg_replace('/([^a-zA-zöőóúüűáéíÖ ŐÓÚÜŰÁÉÍ]*)/is','',$verse);
	$verse = preg_replace('/( ){2,10}/is',' ',$verse);
    $verse = strtolower($verse);
	return $verse;
}
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
    //echo"<pre>"; print_R($output);
    $return = strtolower(strip_tags($return));
    return trim($return);

}

function search($text,$reftrans, $min) {
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
   
   $mysqlrows = dbsearchtext(" verse LIKE '%".$text_verse."%' OR versesimple LIKE '%".$text_versesimple."%' OR verseroot LIKE '%".$text_verseroot."%'  ",$reftrans);
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
  
  $query = " (".implode(' AND ',$where['verse']).") OR (".implode(' AND ',$where['versesimple']).") OR (".implode(' AND ',$where['verseroot']).") ";
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
							//echo $csens."-".$wrap."-".$cell.": => ".$num."\n";
							$results = addresults(array($row['gepi']=>$row),$results,$num);
					}
				}
			}
		}
   }
   if(count($results) > $min * 1.1 ) return resultsorder($results);
   /* darabokan vége */
   $GLOBALS['fullsearch'] = 1;
  //exit;		
   return resultsorder($results);
}

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
        $code = isquotetion($texttosearch);

	if($code)  {
		
		Header( "HTTP/1.1 301 Moved Permanently" ); 
		Header( "Location: ".BASE.$translations[$_REQUEST['reftrans']]['abbrev'].'/'.preg_replace('/ /','',$code['code']));
		exit;
	} else {
		$extra = '';
		if(isset($_REQUEST['offset'])) $extra .= '&offset='.$_REQUEST['offset'];
		if(isset($_REQUEST['rows'])) $extra .= '&rows='.$_REQUEST['rows'];
		
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
		if($uri[1] == 'epub' OR $uri[2] == 'mobi') {
			$q = 'ebook';
			$type = $uri[2];
				
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
?>
