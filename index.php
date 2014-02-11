<?php 
session_start();

require_once('bibleconf.php');
require_once('biblefunc.php');
require_once('func.php');
require_once('quote.php');


/**Hosszú URL átirányítás RÖVIDRE**/
/* Arra is gondol, hogy ha régi-régi url-t kap, mert valaki még arra 
   hivatkozik, akkor az is átjusson a megfelelő oldalra. 
*/   
redirect_long2short();

/**CACHE**/
/* Elég agresszív, de a túlélés miatt csináltam.
   A végén van egy kis kód még, ami kell hozzá. (Az ment.) 
*/
$uri = $_SERVER["REQUEST_URI"];
$cachefile = 'cache/cached-'.md5($uri).'.html';
$cachetime = 18000;
$cachetime = 18000;

if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    echo "<!-- Cached copy, generated ".date('Y-m-d H:i', filemtime($cachefile))." -->\n";
    include($cachefile);
    exit;
}
ob_start(); // Start the output buffer
/**/

header('Content-type: text/html; charset=utf-8'); 

/* Néhány változó alapértelmezett beállítása */
if(isset($_REQUEST['reftrans']) AND is_numeric($_REQUEST['reftrans']) AND $_REQUEST['reftrans'] > 0 ) $reftrans = $_REQUEST['reftrans'];
else $reftrans = 1;
if(isset($_REQUEST['q'])) $q = $_REQUEST['q'];
else $q = 'showbible';
if(isset($_REQUEST['texttosearch']) AND $_REQUEST['texttosearch'] != '') $texttosearch = $_REQUEST['texttosearch'];
else $texttosearch = false; 

/**Rövid URL-ből kiszedjük a változókat**/
$vars = url_short2vars();
foreach($vars as $k=>$v) $$k = $v;

//Sajnos a $transid $reftrans redundáns definíció
if(isset($transid)) $reftrans = $transid;

//A $q változó mondja meg, hogy mit is akarunk betölteni.
//Kb. minden típusú oldal egy-egy $q file tartozik hozzá.
if($q=='showbible') $hirek = getnews();
if($q != false AND file_exists($q.'.php')) require_once($q.'.php');
else {
	$title = 'A kért oldal nem található!';
	$content = 'Elnézést kérünk a kellemetlenségért.<br/>';
}
                     
//Talán csak a statisztikáknál használjuk. Talán.
if(!isset($original) OR $original == '') $original = print_R($_REQUEST,1);


/**MENÜ felépítése**/
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
/**/	
	
	
	$abbrevlist = showbookabbrevlist($reftrans,"");
	
/** URL-ek átírása a szövegekben **/
/* A $content, $title, $abbrevlist-ban előfordulhatnak véletlenül hosszú url-ek */
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
/**/


/**SEGÉD FÜGGVÉNYEK**/
/* Ugyan, miért itt vannak ezek? */	
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

/**Akkor kiírjuk azt, ami eddig bejött.**/
include 'template.php';	


/**CACHE vége**/
/* Cacha mentése file-ban*/
$cached = fopen($cachefile, 'w');
fwrite($cached, ob_get_contents());
fclose($cached);
ob_end_flush(); // Send the output to the browser
/**/
?>
 