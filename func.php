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

function search($text,$reftrans) {
	  $results = array();
 	        
	  foreach(array('verse','versesimple','verseroot') as $cell) {
        if($cell == 'versesimple') $text = simpleverse($text);
        if($cell == 'verseroot') $text = rootverse($text);
        
        /* ahogy van */
		$tmp = dbsearchtext($cell." regexp '([ ,\"\'„]|^)".$text."([\"\' ,.;?!-”]|$)' ",$reftrans);
		$results = addresults($tmp,$results);
	  
        /* beolvadva akár */
		$tmp = dbsearchtext($cell." regexp '([ ,\"\'„]|^)".$text."' ",$reftrans);
		$results = addresults($tmp,$results);
        
        /* beolvadva akár */
		$tmp = dbsearchtext($cell." regexp '".$text."([\"\' ,.;?!-”]|$)' ",$reftrans);
		$results = addresults($tmp,$results);
        
        /* beolvadva akár */
		$tmp = dbsearchtext($cell." regexp '".$text."' ",$reftrans);
		$results = addresults($tmp,$results);
		
        /* részleteire bontva pontosan */
		$segments = explode(' ',$text);
		$resultstmp = array(); $resultstmp2 = array(); 
		foreach($segments as $key => $segment) {
			$tmp = dbsearchtext($cell." regexp '([ ,\"\'„]|^)".$segment."([\"\' ,.;?!-”]|$)' ",$reftrans);
			foreach($tmp as $t) { 
				$resultstmp[] = $t;
				$resultstmp2[$t['gepi']] = $t;
			}
		}
		$mm = array_count_values(array_map(function($item) {
			return $item['gepi'];
		}, $resultstmp));
		
		$resultstmp = array();
		foreach($mm as $gepi => $m) {
			if($m == count($segments)) {
				$resultstmp[$gepi] = $resultstmp2[$gepi];
			}
		}
		$results = addresults($resultstmp,$results);
		
		/* részleteire bontva beolvadva akár */
		$segments = explode(' ',$text);
		$resultstmp = array(); $resultstmp2 = array(); 
		foreach($segments as $key => $segment) {
			$tmp = dbsearchtext($cell." regexp '".$segment."' ",$reftrans);
			foreach($tmp as $t) { 
				$resultstmp[] = $t;
				$resultstmp2[$t['gepi']] = $t;
			}
		}
		$mm = array_count_values(array_map(function($item) {
			return $item['gepi'];
		}, $resultstmp));
		
		$resultstmp = array();
		foreach($mm as $gepi => $m) {
			if($m == count($segments)) {
				$resultstmp[$gepi] = $resultstmp2[$gepi];
			}
		}
		$results = addresults($resultstmp,$results);
		/*
		$prepare = array();
		foreach($resultstmp as $resulttmp) {
			foreach($resulttmp as $tmp) {
				if(array_
			}
		}
		*/
		//echo $shorttext;
		
	  }
	  $order1 = array(); $order2 = array();
	  foreach($results as $key=>$res) {
				$order1[$key] = $res['point'];
				$order2[$key] = $res['gepi'];
                
	 }
	  array_multisort($order1,SORT_DESC, $order2, SORT_ASC, $results);
	  
	  
	  return $results;
}

function addresults($news,$old) {
	foreach($news as $gepi => $new) {
		if(array_key_exists($gepi,$old)) {
			if(isset($old[$gepi]['point'])) $old[$gepi]['point']++;
			else $old[$gepi]['point'] = 1;
		} else {
			$old[$gepi] = $new;
			$old[$gepi]['point'] = 1;
		}
	}
	return $old;
}

function dbsearchtext($query,$reftrans) {
	$return = array();
	$query = "select gepi from ".DBPREF."tdverse  where (".$query.") and ".DBPREF."tdverse.trans=$reftrans";
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
	global $baseurl;
	if(!preg_match('/(http:\/\/|^\/)/i',$url)) {
		$tmp = explode('?',$url);
		if(isset($tmp[1])) {
			$vars = explode('&',$tmp[1]);
			foreach($vars as $key => $var) {
				if(preg_match('/^q=/i',$var)) unset($vars[$key]);
			}
			$tmp[1] = implode('&',$vars);
		}
		$newurl = $baseurl.'index.php?q='.$tmp[0];
		if(isset($tmp[1])) $newurl .= '&'.$tmp[1];
	} else $newurl = $url;

	return $newurl; 
}
?>
