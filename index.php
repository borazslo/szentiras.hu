<?php 
session_start();
header('Content-type: text/html; charset=utf-8'); 

$dolgozunk = false;
//$dolgozunk = true;
//print_R($_REQUEST);

require_once('bibleconf.php');
require_once("biblefunc.php");
require_once('func.php');
require_once('quote.php');

/* Hoszzú URL átirányítás RÖVIDRE */
if(isset($_REQUEST['q']) AND $_REQUEST['q'] == 'showtrans') {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
			if($tdtrans['did'] == $reftrans) {
				Header( "HTTP/1.1 301 Moved Permanently" ); 
				Header( "Location: ".$basurl.$tdtrans['abbrev']); 
				break;
			}
	}
} elseif(isset($_REQUEST['q']) AND $_REQUEST['q'] == 'showbook' AND isset($_REQUEST['abbook']) ) {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
			if($tdtrans['did'] == $reftrans) {
				foreach($books as $book) {
					if($book['abbrev'] == $_REQUEST['abbook'] AND $tdtrans['did'] == $book['reftrans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".$basurl.$tdtrans['abbrev'].'/'.$_REQUEST['abbook']); 
						break;
					}
				}
			}
	}
} elseif(isset($_REQUEST['q']) AND $_REQUEST['q'] == 'showchapter' AND isset($_REQUEST['abbook']) AND isset($_REQUEST['numch'])) {
	if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
	else $reftrans = 1;
	
	foreach($translations as $tdtrans) {
		//echo mb_detect_encoding($_REQUEST['abbook'],'UTF-8','ISO-8859-2');
		//echo iconv('ISO-8859-2','UTF-8',urldecode($_REQUEST['abbook']));
		
			if($tdtrans['did'] == $reftrans) {
				foreach($books as $book) {
					if($book['abbrev'] == $_REQUEST['abbook'] AND $tdtrans['did'] == $book['reftrans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".$basurl.$tdtrans['abbrev'].'/'.$_REQUEST['abbook'].$_REQUEST['numch']); 
						break;
					}
				}
				foreach($books as $book) {
					if($book['abbrev'] == iconv('ISO-8859-2','UTF-8',$_REQUEST['abbook']) AND $tdtrans['did'] == $book['reftrans']) {
						Header( "HTTP/1.1 301 Moved Permanently" ); 
						Header( "Location: ".$basurl.$tdtrans['abbrev'].'/'.iconv('ISO-8859-2','UTF-8',$_REQUEST['abbook']).$_REQUEST['numch']); 
						break;
					}
				}
			}
	}
}



if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
else $reftrans = 1;
if(isset($_REQUEST['q'])) $q = $_REQUEST['q'];
else $q = 'showbible';
if(isset($_REQUEST['texttosearch']) AND $_REQUEST['texttosearch'] != '') $texttosearch = $_REQUEST['texttosearch'];
else $texttosearch = false; 

/* RÖVID URL-ból értelmezhető eredmények */

if(isset($_REQUEST['rewrite']) AND $_REQUEST['rewrite'] != '') {
	$uri = rtrim($_REQUEST['rewrite'],'/');
	$uri = explode('/',$uri);
	if(count($uri)==2 AND $uri[0] == 'kereses') {
		$q = 'searchbible';
		$texttosearch = $uri[1];
	}
	elseif(count($uri) == 2) {
		$isit = isquotetion($uri[1]);
		foreach($translations as $tdtrans) {
			if($tdtrans['abbrev'] == $uri[0]) {
				if($isit != false) {
					foreach($books as $book) $abbrevs[] = $book['abbrev'];
					if(preg_match('/^('.implode('|',$abbrevs).')([0-9]{1,3})$/',$uri[1],$matches)) {
						$q = 'showchapter';
						$reftrans = $tdtrans['did']; 
						$abbook = $matches[1];
						$numch = $matches[2];
						
					} else {
						$q = 'searchbible';
						$reftrans = $tdtrans['did']; 
						$texttosearch = $uri[1];
					}
					break;
				}	
				else {
					foreach($books as $book) {
						if($book['abbrev'] == $uri[1] AND $book['reftrans'] == $tdtrans['did']) {
							$q = 'showbook';
							$reftrans = $tdtrans['did'];
							$abbook = $uri[1];
							break;
						}
					}					
				}
			}
		}
		
		
		
	
		$isit = isquotetion($uri[1]);
		foreach($translations as $tdtrans) {
			
		}
	}
	elseif(count($uri)==1 ) {
		$go = false;
		if($uri[0] == 'kereses') {
			$go = true;
			$q = 'searchbible';
		}
		if($go == false) {
		foreach($translations as $tdtrans) {
			if($tdtrans['abbrev'] == $uri[0]) {
				$q = 'showtrans';
				$reftrans = $tdtrans['did']; 
				$go = true;
				break;
			}
		} }
		if($go == false) {
			$isit = isquotetion($uri[0]);
			if($isit != false) {
				$q = 'searchbible';
				$texttosearch = $uri[0];
				$reftrans = $isit['reftrans'];
			}
		}
	}
	
	if($uri[0] == 'API') {
		$q = 'api';
		$api = $uri[1];
	}
	
}

if($q != false AND file_exists($q.'.php')) require_once($q.'.php');
else {
	$title = 'A kért oldal nem található!';
	$content = 'Elnézést kérünk a kellemetlenségért.';
}

$menu = new Menu();
	//$menu->add_item("Bibliaolvasás","showbible");
	$menu->add_item("Bibliaolvasás",$baseurl);
	$menu->add_item("Keresés a Bibliában",$baseurl.'kereses');
	$menu->add_pause();
	foreach($translations as $tdtrans) {
		$menu->add_item($tdtrans['name']." (".$tdtrans['abbrev'].")",$baseurl.$tdtrans['abbrev']);
		$translationIDs[$tdtrans['did']] = $tdtrans;
	}
	$menu->add_pause();

	$form .= "<form action='".$baseurl."index.php' method='get'>\n";
		$form .= "<input type='hidden' name='q' value='searchbible'>\n";
		$form .= "<input type='hidden' name='reftrans' value='".$reftrans."'>\n";
		$form .= "<input type=text name='texttosearch' size=10 maxlength=80 value='".$texttosearch."' class='alap' style='width:92%;margin-bottom:5px'>\n";
		$form .= "<input type=submit value='Keresés' class='alap'>\n";
		$form .= "</form>\n";
	
	$menu->add_item("Görög újszövetségi honlap","http://www.ujszov.hu/");
	$menu->add_item("Újszövetség: hangfájlok","http://www.kereszteny.hu/biblia/hang/");
	//$menu->add_item("A templom egere","http://templom-egere.kereszteny.hu/");
	$menu->add_pause();
		$menu->add_text($form);
		
	
	$menu->add_pause();
	$menu->add_item("FEJLESZTŐKNEK",$baseurl."API");
	$menu->add_item("Újdonságaink","info");
	/*
	$menu->add_item("Katolikus igenaptár","http://www.katolikus.hu/igenaptar/");
	$menu->add_item("Zsolozsma","http://zsolozsma.katolikus.hu/");
	*/
	$abbrevlist = showbookabbrevlist($db,$reftrans,"");
	
	/* URL-ek átírása a szövegekben */
	foreach(array('content','title','abbrevlist') as $var) {
		$text = $$var;
		$pattern = '/(\\\'|")'.addcslashes($baseurl,'./').'index\.php\?q=showtrans&reftrans=([0-9]{1,2})(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showtrans',$text);
		$pattern = '/(\\\'|")'.addcslashes($baseurl,'./').'index\.php\?q=showbook&reftrans=([0-9]{1,2})&abbook=(.*?)(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showbook',$text);
		$pattern = '/(\\\'|")'.addcslashes($baseurl,'./').'index\.php\?q=showchapter&reftrans=([0-9]{1,2})&abbook=(.*?)&numch=([0-9]{1,3})(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showchapter',$text);
		
		$$var = $text;
	}
	
	function url_showtrans($m) {
			global $baseurl, $translationIDs;
			return $m[1].$baseurl.$translationIDs[$m[2]]['abbrev'].$m[1];
		}
	function url_showbook($m) {
			global $baseurl, $translationIDs;
			return $m[1].$baseurl.$translationIDs[$m[2]]['abbrev']."/".$m[3].$m[1];
		}
	function url_showchapter($m) {
			global $baseurl, $translationIDs;
			return $m[1].$baseurl.$translationIDs[$m[2]]['abbrev']."/".$m[3].$m[4].$m[1];
		}
	/*
	$content = preg_replace('/biblia\/([a-z]*?)\.php(\?|)/','biblia2/INDEX?q=$1&',$content);
	$content = preg_replace('/(=\'|=")([a-z]*?)\.php(\?|)/','$1INDEX?q=$2&',$content);
	$content = preg_replace('/(=\'|=")(http:\/\/www\.kereszteny\.hu\/biblia2\/)(.*?)\.php\?(.*?)(\'|")/i','$1$2INDEX?q=$3&$4$5',$content);
	$content = preg_replace('/INDEX/s','index.php',$content);
	*/
	
	//$meta .= '<meta property="og:description" content="The Rock" />';

include 'template.php';	
?>
