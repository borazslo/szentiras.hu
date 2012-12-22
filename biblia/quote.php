<?php
//error_reporting(-1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

if(array_key_exists('quotation',$_GET)) $code = $_GET['quotation']; else $code = ""; //"1Kor 7, 25-28. 30; 2, 12";
if(array_key_exists('reftrans',$_GET)) $trans = $_GET['reftrans']; else $trans = 1;

if(mb_detect_encoding($code,'UTF-8, ISO-8859-2') == 'ISO-8859-2') $code = iconv("ISO-8859-2",'UTF-8',$code);

$query = $code;


$verses = array();

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
		$verses = print_verses($verses);
		
		if($verses == "<span class='alap'></span>	") { $return .= iconv("UTF-8", "ISO-8859-2","Nincs találat.");}
		else		$return .= $verses;
	}
	if(in_array('errors',$args)) {$return .="<br>".print_errors($error); }
	return $return;
}
	
function print_form() {
		global $code;
		global $trans;
		global $query;
			
		global $base;
		$return = '<form name="input" action="'.$_SERVER['PHP_SELF'].'" method="get">
			<input type="text" name="quotation" value="'.$query.'" /><br />';
			
			$transs = db_query("SELECT * FROM tdtrans");
			
			/* RADIO BUTTON type *
			foreach($transs as $t) {
				$return .= '<input type="radio" name="reftrans" value="'.$t['did'].'" ';
				if($trans == $t['did']) $return .= "checked"; 
			$return .= '/> <span class="alap">'.$t['name'].' </span>';
			/* */
			
			/* SELECT type */
			$return .='<select name="reftrans">';
			foreach($transs as $t) {
				$return .= '<option value="'.$t['did'].'"';
				if($trans == $t['did']) $return .= " selected=\"selected\" "; 
				$return .= '>'.$t['name'].'</option>';
			}
			$return .='</select>';
			/* */
			
		$return .= '</form>';
		return $return;
}

function quotetion($arg1 = '',$arg2 = '',$arg3 = '',$arg4 = '',$arg5 = '',$arg6 = '',$arg7 = '',$arg8 = '')  {
	$args = array();
	for($i = 1;$i<9;$i++) {
		if(${'arg'.$i} != '') $args[] = ${'arg'.$i};
	}
	global $code;
	global $error;
	global $book;
	global $verses;
	global $trans;
	global $query;
	
	if(!in_array('html',$args) AND !in_array('array',$args) AND !in_array('json',$args)) $args = array_merge($args,array('html'));
	if(!in_array('title',$args) AND !in_array('form',$args) AND !in_array('verses',$args)) $args = array_merge($args,array('title','form','verses'));
	
	
	$aa = array('html','json','array','title','form','verses','errors');
	$tmp = array();	
	
	foreach($args as $k=>$i) {
		if(!in_array($i,$aa)) $tmp[] = $i;
	}
	foreach($tmp as  $t) {
		if(is_numeric($t)) $trans = $t;
		else $code = $query = $t;
	}

	$error = array();
	/* ellenőrzés, hogy semmi spéci karakter ne legyen benne */
	
	if(!preg_match('/^([a-zA-Z0-9éáóőöüűúí ÉÁÖÓŐÜŰÚÍ,;\.\-]+)$/i',$code)) { $error[] = "Wrong characters in the input."; }
	else {
	
	/* szünetek eltávolítása */
	$code = preg_replace('/ /','',$code);
	/* könyv kiszűrése */
	preg_match('/^(\d){0,1}([^;\.\-\,0-9]*)/',$code,$match);
	
	if($match[0]=='') $error[] = "There is no book...";
	else {
	$mysqlquery = "SELECT * FROM tdbook WHERE reftrans = $trans AND abbrev = '".iconv('UTF-8','ISO-8859-2',$match[0])."' LIMIT 0,1";
	$books = db_query($mysqlquery);
	if(!is_array($books)) { 
		$tmp = preg_replace('/([\d]{1})(.*)/','$1 $2',$match[0]);
		$books = db_query("SELECT * FROM tdbook WHERE reftrans = $trans AND abbrev = '".iconv('UTF-8','ISO-8859-2',$tmp)."' LIMIT 0,1");
	}
	if(!is_array($books)) {  $error[] = "Wrong abbrevation of the book";  
		global $tipps;
		
		$select = "SELECT * FROM szinonimak WHERE tipus = 'konyv' AND (binary szinonimak LIKE '%|".preg_replace('/ /','',iconv('UTF-8','ISO-8859-2',$tmp))."|%' OR binary  szinonimak LIKE  '%|".preg_replace('/ /','',iconv('UTF-8','ISO-8859-2',$tmp)).":%');";
		$result = db_query($select); $szinonima = array();
		global $baseurl;
		if(is_array($result)) foreach($result as $r) {
			$szin = explode('|',$r['szinonimak']);
			foreach($szin as $sz) {
				$s = explode(':',$sz);
				if($s[0] != '' AND $s[0] != $tmp AND !in_array($s[0],$szinonima) ) {
					global $reftrans;
					if(isset($s[1]) AND $s[1] == $reftrans) {
						$szinonima[] = $s[0];
					}
				}
			}
		}
		if($szinonima != array()){
		$return = iconv("UTF-8",'ISO-8859-2',"Próbáld meg így: ");
		$c = 1;
		foreach($szinonima as $szin) {
			//$szin amiben nincs szóköz
			//$tmp amiben van
			//$query amiben vagy van, vagy nincs
			$szin1 = preg_replace('/([1-4]{1})[ ]{0,1}(.*)/','$1 $2',$szin);
			$tmp1 = preg_replace('/([1-4]{1})[ ]{0,1}(.*)/','$1 $2',$tmp);
			$query1 = preg_replace('/(^[1-4]{1})[ ]{0,1}(.*)/','$1 $2',$query);
			$new = preg_replace('/'.$tmp1.'/',$szin1,$query1);
			//echo $new."::".$tmp1."||".$szin1."||".$query1;
			
			
			$return .= " <a href='".$baseurl."searchbible.php?texttosearch=".$new."&reftrans=".$reftrans."' class=link>".$new."</a>";		
			if($c<count($szinonima)) $return .= ',';
			$c++;
		}
		$return .= '!';
		
		//$tipps[] = iconv("UTF-8",'ISO-8859-2',$return);
		$tipps[] = $return;
		}
	}
	else { $book = $books[0]['did'];
	$code = preg_replace('/^(\d){0,1}([^;\.\-\,0-9]*)/','',$code);
	
	/* Kiszedjük belőle az a,b,c stb. részlet utalásokat, mert az adatbázisunk nem kezeli azt */
	$code = preg_replace('/[a-f]{1}/','',$code);
	
	
	/* részek szétszedése ; alapján */
	$codes = explode(';',$code);
	foreach($codes as $code) {
		/* fejezeteken át, vagy sem */
		if(count(explode(',',$code))>2) {
			/* fejezeteken át */
			preg_match('/^([\d]{1,2}),(.*)-([\d]),(.*)/',$code,$match);
			for($i=$match[1];$i<$match[3];$i++) {
				$lastv = db_query("SELECT lastv FROM tdchapter as c, tdbook as b WHERE c.reftrans = b.reftrans AND c.abbook = b.abbrev AND b.did = $book AND numch = ".$i." LIMIT 0,1");
				if(!is_array($lastv)) { $error[] = "There is no so many chapter in the book";
				$lastv = array(); $lastv[0]['lastv'] = 100;
				}
				if($match[1] == $i) add_verses($i.",".$match[2]."-".$lastv[0]['lastv']);
				else add_verses($i.",1-".$lastv[0]['lastv']);
			}
			add_verses($match[3].",1-".$match[4]);
		} elseif(count(explode(',',$code)) == 1) {
			$lastv = db_query("SELECT lastv FROM tdchapter as c, tdbook as b WHERE c.reftrans = b.reftrans AND c.abbook = b.abbrev AND b.did = $book AND numch = ".$code." LIMIT 0,1");
			if(!is_array($lastv)) { $error[] = "There is no so many chapter in the book";
				$lastv = array(); $lastv[0]['lastv'] = 100;
				}
			add_verses($code.",1-".$lastv[0]['lastv']);
			}
		else {
			/* fejezeten belül */
			add_verses($code);
		}
	}
	}
	}
	}
	
	if(in_array('html',$args)) return print_quotetion($args);	
	elseif(in_array('json',$args)) return json_encode(array('verses'=>$verses,'errors'=>$errors,'query'=>$query));
	elseif(in_array('array',$args)) return array('verses'=>$verses,'error'=>$error);
}

function add_verses($code,$start = false) {

	global $verses;
	global $book;
	global $trans;
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
				$verses[] = get_verse($book,$chapter,$i,$trans,$start);
			}
		} else {
		if($s) { $start = true; $s = false;} else $start = false;
		$verses[] = get_verse($book,$chapter,$t,$trans,$start);
		}
	}
}

function get_verse($book,$chapter,$verse,$trans	= 1,$start = false) {
	global $error;
	
	$return = array();
	
	$verses = db_query("SELECT v.verse, b.* FROM tdverse as v, tdbook as b WHERE v.numv = $verse AND v.numch = $chapter AND v.reftrans = $trans AND b.abbrev = v.abbook AND b.reftrans = $trans AND b.did = $book LIMIT 0,1");
	
	if($verses != 1) { $return['verse'] = $verses[0]['verse']; }
	else { $error[] = "There is no verse found."; return false;}
	if($start != false) $return['start'] = true;
	$return['query'] = array("book"=>$book,"chapter"=>$chapter,"verse"=>$verse,"trans"=>$trans,'abbrev'=>$verses[0]['abbrev']);
	
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
		if(array_key_exists('start',$verse) OR $k == 0) $return .= " <strong>".$verse['query']['chapter']."</strong> ";
		$return.= " <sup>".$verse['query']['verse']."</sup>".$verse['verse']." ";
		}
	}
	$return .= "</span>	";
	return $return;
}

/* 
 * Adatbázis kezelő függvények. Általában db.php néven...
 *
 *
 */
 
 function db_connect() {
	
	$user="root";
	$password="Felpecz";
	$database="bible";

	if($_SERVER['HTTP_HOST'] == 'localhost') $password = '';
	$db_link = mysql_connect('localhost:3306',$user,$password) or die ("Can't connect to mysql");
	
	//mysql_set_charset('utf8');
	if ($db_link) @mysql_select_db($database);
	return $db_link;
}

function db_close() {
	if(isset($db_link)) { $result = mysql_close($db_link);
	return $result;
	}
}

function db_query($query,$debug = '',$return = '') {
	
	if($debug == 'x' or $debug == '') $debug = 0;
	 /* 
	  * Debug 1 -> ha hiba van jelzi
	  * Debug 2 -> mindenképp közöl valamit
	  * FIGYELEM! a debug 2 kilövi az ajax_framewokr.js-t mert a mysqltoxml.php nem xml válasza miatt!
	  *
	  */
	  db_close();
	
	db_connect();
	
	if(!($result = mysql_query($query))) $error = mysql_errno().": ".mysql_error()." (<i>$query</i>)\n<br>";
	
	if($debug==1 and isset($error)) echo $error;
	elseif($debug==2 and !isset($error)) echo $query."<br>\n";
	elseif($debug==2) echo $error;

	//FIXIT: insert eset�n nem megy a fetch, de akkor nincs error hadling;
	if(is_bool($result)) return;
	$rows = array();
	while(($row = mysql_fetch_array($result,  MYSQL_ASSOC))) {
		foreach($row as $k => $i) {
			//$row[$k] = iconv("ISO-8859-1", "UTF-8", $i);
			$row[$k] = $i;
		}
		$rows[] = $row;
	}
	if($rows!=array()) return $rows;
	
	//echo "++".mysql_affected_rows()."++";
	/*
	 * Ezt itten kivételeztem.
	 */
	//if(!isset($error) AND isset($return)) return $return();
	db_close();
	if(isset($error)) return false;
	else return true;

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
?>
