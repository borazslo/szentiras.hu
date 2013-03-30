<?php
//fontos!! http://www.wordle.net/delete?index=6194253&d=LOWD
session_start();

  /*
   * Default values
   */
  $min = 20;
  $max = 100;
  if (isset($_REQUEST['offset'])) $offset = $_REQUEST['offset']; else $offset = 0;
  if (isset($_REQUEST['rows'])) $rows = $_REQUEST['rows']; else $rows = 50;
  if (isset($_REQUEST['reftrans'])) $reftrans = $_REQUEST['reftrans']; else $reftrans = 1;
  /*
  require("../include/design.php");
  require("../include/biblemenu.php");
  require("../include/bibleconf.php");
  require("../include/biblefunc.php");
  */
//  require("JSON.php"); /* PHP 5.2 >= esetén */
include 'quote.php';

  //portalhead("Wordle.net");
  //bibleleftmenu();

  
  //echo'<form action="http://www.wordle.net/advanced" method="POST"><textarea name="text" style="display:none">';
  
  //Common from: https://raw.github.com/jdf/cue.language/master/src/cue/lang/stop/hungarian
  $common = array('a','ahogy','ahol','aki','akik','akkor','alatt','által','általában','amely','amelyek','amelyekben','amelyeket','amelyet','amelynek','ami','amit','amolyan','amíg','amikor','át','abban','ahhoz','annak','arra','arról','az','azok','azon','azt','azzal','azért','aztán','azután','azonban','bár','be','belül','benne','cikk','cikkek','cikkeket','csak','de','e','eddig','egész','egy','egyes','egyetlen','egyéb','egyik','egyre','ekkor','el','elég','ellen','elő','először','előtt','első','én','éppen','ebben','ehhez','emilyen','ennek','erre','ez','ezt','ezek','ezen','ezzel','ezért','és','fel','felé','ha','hanem','hiszen','hogy','hogyan','igen','így','illetve','ill.','ill','ilyen','ilyenkor','inkább','is','ison','ismét','itt','jó','jól','jobban','kell','kellett','keresztül','keressünk','ki','kívül','között','közül','legalább','lehet','lehetett','legyen','lenne','lenni','lesz','lett','maga','magát','majd','majd','már','más','másik','meg','még','mellett','mert','mely','melyek','mi','mit','míg','miért','milyen','mikor','minden','mindent','mindenki','mindig','mint','mintha','mivel','most','nagy','nagyobb','nagyon','ne','néha','nekem','neki','nem','néhány','nélkül','nincs','olyan','ott','össze','ő','ők','őket','pedig','persze','rá','s','saját','sem','semmi','sok','sokat','sokkal','számára','szemben','szerint','szinte','talán','tehát','teljes','tovább','továbbá','több','úgy','ugyanis','új','újabb','újra','után','utána','utolsó','vagy','vagyis','valaki','valami','valamint','való','vagyok','van','vannak','volt','voltam','voltak','voltunk','vissza','vele','viszont','volna');

  $common2 = array('íme','én','ti','te','se');
  
  $bible = array();
  
  $exludes = array_merge($common,$common2,$bible);
  
  
  $szavak = array();
  for($i=0;$i<100;$i++) {

	$adag = 1000;
	//$query = 'SELECT verse FROM tdverse WHERE reftrans = 3 LIMIT '.($i*$adag).','.($adag);
	$query = '
		SELECT verse 
			FROM tdverse,tdbook 
			WHERE tdverse.reftrans = 3 AND tdverse.abbook = abbrev AND oldtest < 2 
			ORDER BY bookorder 
			LIMIT '.($i*$adag).','.($adag);
	//echo "XXX".$i."XXX";
	//ujszov 47-től
	//echo $query."<br>";
	
	$rows = db_query($query);
	foreach($rows as $row) {
		set_time_limit(30);
		$verse = $row['verse'];
		$verse = preg_replace("/[^A-Za-z0-9öüóőúűáéíÖÜÓŐÚÉÁŰÍ]/i",' ',$verse);
	
		$tmp = explode(' ',$verse);
		foreach($tmp as $szo) {
			$ss = strtolower($szo);
			$ss = preg_replace(array('/É/i','/Á/i','/Í/i','/Ú/i','/Ő/i',),array('é','á','í','ú','ő'),$ss);
			if(strlen($szo)>1 AND !in_array($ss,$exludes)) {
				if(array_key_exists($szo,$szavak)) $szavak[$szo]++;
				else $szavak[$szo] = 1;
				}
		}
	}
	
  }
  arsort($szavak);
  $c=0;
  foreach($szavak as $szo=>$szam) {
	$c++; if($c < 400) {
		echo $szo.":".$szam."<br>\n";	
	}
  }
  
  //echo'</textarea>    <input type="submit"></form>';
  
  //portalfoot();

?>