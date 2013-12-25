<?php 
//require_once('/var/www/szentiras.hu/include/php-ga-1.1.1/src/autoload.php');
//use UnitedPrototype\GoogleAnalytics;
// Initilize GA Tracker
//$tracker = new GoogleAnalytics\Tracker('UA-36302080-1', 'szentiras.hu');
// Assemble Visitor information
// (could also get unserialized from database)
//$visitor = new GoogleAnalytics\Visitor();
// Assemble Session information
// (could also get unserialized from PHP session)
//$session = new GoogleAnalytics\Session();

//$event = new GoogleAnalytics\Event('','','');

session_start();
header('Content-type: text/html; charset=utf-8'); 

require_once('bibleconf.php');
require_once("biblefunc.php");
require_once('func.php');
require_once('quote.php');


/* Hosszú URL átirányítás RÖVIDRE */

redirect_long2short();


/* */

if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
else $reftrans = 1;
if(isset($_REQUEST['q'])) $q = $_REQUEST['q'];
else $q = 'showbible';
if(isset($_REQUEST['texttosearch']) AND $_REQUEST['texttosearch'] != '') $texttosearch = $_REQUEST['texttosearch'];
else $texttosearch = false; 

$vars = url_short2vars();
foreach($vars as $k=>$v) $$k = $v;
if(isset($transid)) $reftrans = $transid;
//print_r($vars);

//echo $q;
if($q=='showbible') $hirek = getnews();
if($q != false AND file_exists($q.'.php')) require_once($q.'.php');
else {
	$title = 'A kért oldal nem található!';
	$content = 'Elnézést kérünk a kellemetlenségért.<br/>';
}
                     
if(!isset($original) OR $original == '') $original = print_R($_REQUEST,1);

$menu = new Menu();
	//$menu->add_item("Bibliaolvasás","showbible");
	//$menu->add_item("Bibliaolvasás",BASE);
	//$menu->add_pause();
	foreach($translations as $tdtrans) {
        if($tdtrans['denom'] == 'katolikus')
            $menu->add_item($tdtrans['name']." (".$tdtrans['abbrev'].")",BASE.$tdtrans['abbrev']);
		$translationIDs[$tdtrans['id']] = $tdtrans;
	}
   
	if(!isset($form)) $form = '';
	$form .= "<form action='".BASE."index.php' method='get'>\n";
		$form .= "<input type='hidden' name='q' value='searchbible'>\n";
		$form .= "<input type='hidden' id='reftrans' name='reftrans' value='".$reftrans."'>\n";
		$form .= "<input type=text name='texttosearch' id='texttosearch'  onkeyup=\"suggest(this.value);\" size=10 maxlength=80 value='".$text."' class='alap' style='width:92%;margin-bottom:5px'>\n";
		$form .= "<input type=submit value='Keresés' class='alap'>\n";
				$form .= '<div id="suggestions" class="suggestionsBox2" style="display: none;">
<!-- <img style="position: relative; top: -12px; left: 30px;" src="arrow.png" alt="upArrow" />-->
<div id="suggestionsList" class="suggestionList"></div>
</div>';
		$form .= "</form>\n";
	
	$menu->add_item("Újszövetség: hangfájlok",BASE."hang/");
	//$menu->add_item("A templom egere","http://templom-egere.kereszteny.hu/");
    $menu->add_item("További fordítások",BASE."forditasok");

    $menu->add_pause();
    $menu->add_item("Keresés a Bibliában",BASE.'kereses');
	if($q != 'searchbible' OR $text != '') { $menu->add_text($form); }    
	$menu->add_pause();
    
	$menu->add_item("FEJLESZTŐKNEK",BASE."API");
	$menu->add_item("Újdonságaink",BASE."info/");
	
    $menu->add_pause();
	$menu->add_item("Görög újszövetségi honlap","http://www.ujszov.hu/");
	$menu->add_item("Katolikus igenaptár","http://www.katolikus.hu/igenaptar/");
	$menu->add_item("Zsolozsma","http://zsolozsma.katolikus.hu/");
	
	$abbrevlist = showbookabbrevlist($reftrans,"");
	
	/* URL-ek átírása a szövegekben */
	foreach(array('content','title','abbrevlist') as $var) {
		$text = $$var;
		$pattern = '/(\\\'|")'.addcslashes(BASE,'./').'index\.php\?q=showtrans&reftrans=([0-9]{1,2})(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showtrans',$text);
		$pattern = '/(\\\'|")'.addcslashes(BASE,'./').'index\.php\?q=showbook&reftrans=([0-9]{1,2})&abbook=(.*?)(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showbook',$text);
		$pattern = '/(\\\'|")'.addcslashes(BASE,'./').'index\.php\?q=showchapter&reftrans=([0-9]{1,2})&abbook=(.*?)&numch=([0-9]{1,3})(\\\'|")/i';
		$text = preg_replace_callback($pattern,'url_showchapter',$text);
		//$pattern = '/(\\\'|")'.addcslashes(BASE,'./').'index\.php\?q=searchbible&reftrans=([0-9]{1,2})&texttosearch=(.*?)/i';
		$pattern = '/(\\\'|")'.addcslashes(BASE,'./').'index\.php\?q=searchbible&texttosearch=(.*?)&reftrans=([0-9]{1,2})(.*)$/i';
		$text = preg_replace_callback($pattern,'url_searchbible',$text);
		
		$$var = $text;
	}
	
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
	/*
	$content = preg_replace('/biblia\/([a-z]*?)\.php(\?|)/','biblia2/INDEX?q=$1&',$content);
	$content = preg_replace('/(=\'|=")([a-z]*?)\.php(\?|)/','$1INDEX?q=$2&',$content);
	$content = preg_replace('/(=\'|=")(http:\/\/www\.kereszteny\.hu\/biblia2\/)(.*?)\.php\?(.*?)(\'|")/i','$1$2INDEX?q=$3&$4$5',$content);
	$content = preg_replace('/INDEX/s','index.php',$content);
	*/
	
	//$meta .= '<meta property="og:description" content="The Rock" />';

include 'template.php';	
?>
 