<?php


 $title = 'Fejlesztőknek';
 
//$content = $api;

$content  .= <<<EOD
<br><p class="alcim">Widgetek, pluginek, stb.</p>
<p>Egyelőre semmilyen widget/plugin sem áll rendelkezésre. Ha bármilyen ötlete vagy igénye van szívesen segítünk a kifejlesztésében. 
Létező munkáját kérjük ossza meg velünk, hogy másoknak is hasznára lehessen.</p>
<p>Kellő jelentkező/igénylő/használó esetén kifejleszthetünk egy <a href="http://reftagger.com/">reftagger.com</a> szerű eszközt is. Kérjük jelezze, ha használna ilyet.</p>

<br><p class="cim">API</p>
<p>Az egyes szentírási szakaszokat távolról is el lehet érni <a href="http://hu.wikipedia.org/wiki/JSON">JSON</a> formátumban. Például: <a href="http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor13,10-13">szentiras.hu/API/?feladat=<i>idezet</i>&hivatkozas=<i>1Kor13,10-13</i></a> hivatkozás bekérésével. További részletek, lehetőségek, és PHP-ben alkalmazásra példa lejjebb az API dokumentációban.</p>
<p>Kérésre elérhetővé tesszük XML vagy bármely más közismert formátumban is.</p>

<br><p class="alcim">API: általános leírás</p>
<p>A kéréseket a <a href="http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor13,10-13">szentiras.hu/API/?feladat=<i>feladat</i>&<i>beállítás</i>=<i>beállításértéke</i></a> hivatkozás meghívásával kell elindítani. Ez a lekérés egyelőre mindig egy JSON objektummal tér vissza. 
<ul>A visszatérő objektumban szerepel egy 
	<li>'keres': ami tartalmazza a lekérdezés adatait</li>
	<li>'valasz': ami az eredményeket tartalmazza, hiba esetén üres</li>
	<li>'hiba': abban az esetben, ha valami hiba törént. </li></ul>
Például:</p>
<div style="font-size: 10px;
line-height: 13px;
background-color: rgba(0,0,0,0.1);">
{<br>
&nbsp;&nbsp;&nbsp;"keres":<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"feladat":"idezet",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"hivatkozas":"1Kor13,10",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"forma":"json"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;},<br>
&nbsp;&nbsp;&nbsp;"valasz":<br>
&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"hely":<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"gepi": "20701301000"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"szep": "1Kor 13,10"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"szoveg": "amikor pedig eljön majd a tökéletes, a töredékes véget fog érni."<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;],<br>
&nbsp;&nbsp;&nbsp;"forditas":<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"nev":"Káldi-Neovulgáta",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"rov":"KNB",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;},<br>
</div>


<br><a name="beallitasok"></a><p class="alcim">API: lehetséges beállítások</p>
<p><strong>feladat: idezet</strong><br>Adott szentírási szakasz megjelenítése.<br>
<u>hivatkozas</u> (kötelező): pl. <i>1Kor13,10</i><br>
<u>forditas</u>: pl. <i>KNB</i> vagy <i>UF</i><br>
Példa: <a href='http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor13,10-13'>http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor13,10-13</a></p>
<p><strong>feladat: forditasok</strong><br>Egyetlen szentírási vers megjelenítése az összes elérhető fordításban<br>
<u>hivatkozas</u> (kötelező): pl. <i>10100100200</i> (Jelenleg csak az úgynevezett <i>gépi kód</i>dal lehet verset keresni. 
Ez 11 számjegyből áll: 1. Ószöv/Újszöv,  2-3. könyv, 4-6. fejezet, 7-9. vers, 10-11. alvers.)<br>
Példa: <a href='http://szentiras.hu/API/?feladat=forditasok&hivatkozas=10100100100'>http://szentiras.hu/API/?feladat=forditasok&hivatkozas=10100100100</a></p>

<br><a name="php"></a><p class="alcim">API: PHP példa</p>
<div style="font-sie: 13px;
line-heigt: 16px;
background-color: rgba(0,0,0,0.1);">
EOD;

$content .= highlight_string("<?php
	\$json = file_get_contents('http://szentiras.hu/API/?feladat=forditasok&hivatkozas=10100100100');
	\$data = json_decode(\$json, TRUE);
	echo \"<pre>\".print_r(\$data,1).\"</pre>\";
?>",TrUE);
$content .= '</div><br><br>';

$content .='
<div style="font-sie: 13px;
line-heigt: 16px;
background-color: rgba(0,0,0,0.1);">';

$content .= highlight_string("<?php
	\$json = file_get_contents('http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor2,10-14');
    \$data = json_decode(\$json, TRUE);
	
	if(\$data['valasz'] == '') echo '<strong>Hiba történt:</strong> '.\$data['hiba'];
	else {
		echo \"<strong>1Kor2,10-14:</strong><br>\\n\";
		foreach(\$data['valasz']['versek'] as \$vers) {
			echo \$vers['szoveg'].\" \";
		}
	}
?>",TrUE);
$content .= '</div><br><br>';




$errors = array();

if(isset($_REQUEST['forditas'])) {
	foreach($translations as $tdtrans) {
			if($tdtrans['abbrev'] == $_REQUEST['forditas']) {
				$forcedtrans = $tdtrans['did']; 
				break;
			}
		}
	if(!isset($forcedtrans)) {
		header('Content-type: application/json');
		require_once("include/JSON.php");
		$errors[] = 'Nem létező fordítás';
		echo json_encode(array('error'=>$errors));
		exit;
	}
}

if(isset($_REQUEST['feladat']) AND $_REQUEST['feladat'] == 'forditasok') {
		if(isset($_REQUEST['hivatkozas']) AND is_numeric($_REQUEST['hivatkozas'])) {
			$results = db_query("SELECT * FROM tdverse WHERE gepi = ".$_REQUEST['hivatkozas']);
			foreach($results as $vers) {
				$v['hely']['gepi'] = $vers['gepi'];
				$v['hely']['szep'] = $vers['abbook']." ".$vers['numch'].",".$vers['numv'];
				$v['szoveg'] = $vers['verse'];
				$v['forditas']['nev'] = $translations[$vers['reftrans']]['name'];
				$v['forditas']['rov'] = $translations[$vers['reftrans']]['abbrev'];
				//$v['forditas']['nyelv'] = $translations[$vers['reftrans']]['lang'];
				$verses[] = $v;
			}
			$return = array(
				'keres'=>array(
					'feladat' => 'forditasok',
					'hivatkozas' => $_REQUEST['hivatkozas']),
				'valasz'=> $verses);
			
			//echo "<pre>".print_R($return,1)."</pre>";
		} elseif(isset($_REQUEST['hivatkozas']) AND !is_numeric($_REQUEST['hivatkozas'])) {
			
			$return = array(
				'keres'=>array(
					'feladat' => 'forditasok',
					'hivatkozas' => $_REQUEST['hivatkozas']),
				'valasz'=> false,
				'hiba' => 'Még nem vagyunk felkészülve erre a feladatra.');
	}
	else 
		$return = array(
				'keres'=>array(
					'feladat' => 'forditasok',
					'hivatkozas' => false),
				'valasz'=> false,
				'hiba' => 'Nem atdál meg hivatkozást!');
}
elseif(isset($_REQUEST['feladat']) AND $_REQUEST['feladat'] == 'idezet') {
	if(isset($_REQUEST['hivatkozas'])) {
		if(isset($forcedtrans)) $code = isquotetion($_REQUEST['hivatkozas'],$forcedtrans);
		else $code = isquotetion($_REQUEST['hivatkozas']);
		if(is_array($code) AND isset($code['tag'][0]['numv'])) {
			$results = quotetion(array('verses','array',"code"=>$code));
			$verses = array();
			foreach($results['verses'] as $vers) {
				$v['hely']['gepi'] = $vers['gepi'];
				$v['hely']['szep'] = $vers['abbook']." ".$vers['numch'].",".$vers['numv'];
				$v['szoveg'] = $vers['verse'];
				$fordit['nev'] = $translations[$vers['reftrans']]['name'];
				$fordit['rov'] = $translations[$vers['reftrans']]['abbrev'];
				//$fordit['nyelv'] = $translations[$vers['reftrans']]['lang'];
				$verses[] = $v;
			}
			$return = array(
				'keres'=>array(
					'feladat' => 'idezet',
					'hivatkozas' => $_REQUEST['hivatkozas']),
				'valasz'=> array(
					'versek' =>$verses,
					'forditas' => $fordit));
			
		} else {
			$return = array(
				'keres'=>array(
					'feladat' => 'idezet',
					'hivatkozas' => $_REQUEST['hivatkozas']),
				'valasz'=> false,
				'hiba' => 'Hibás hivatkozás vagy nem mefelelő fordítás!');
		}
		if(isset($forcedtrans)) $return['keres']['forditas'] = $translations[$forcedtrans]['abbrev'];
		
	
	} else {
		$return = array(
				'keres'=>array(
					'feladat' => 'idezet',
					'hivatkozas' => false),
				'valasz'=> false,
				'hiba' => 'Nem atdál meg hivatkozást!');
	}
} elseif(isset($_REQUEST['feladat'])) {
	$return = array(
				'keres'=>array(
					'feladat' => $_REQUEST['feladat'],
					'hivatkozas' => false),
				'valasz'=> false,
				'hiba' => 'Ilyen feladat nem létezik');
}

if(isset($return)) {
	require_once("include/JSON.php");
	if(!isset($_REQUEST['forma']) OR !in_array($_REQUEST['forma'],array('json','tomb'))) $forma = 'json'; else $forma = $_REQUEST['forma'];
	$return['keres']['forma'] = $forma;
	
	global $tipps;
	$tipps[] = 'API ';
	foreach(array('feladat','hivatkozas','forditas','forma') as $var) if(isset($_REQUEST[$var])) $insert .= $var.":".$_REQUEST[$var]."|";
	
	if(isset($return['hiba'])) {
		$tipps[] = json_encode($return['hiba']);
		insert_stat($insert,$reftrans,-1);
	} else {
		$tipps[] = json_encode($return['valasz']);
		insert_stat($insert,$reftrans,0);
	}
	
	if($forma == 'json') {
		
		header('Content-type: application/json');
		echo json_encode($return);
		exit;
	}
	elseif($forma == 'tomb') {
		echo "<pre>".print_R($return,1)."</pre>";
		exit;
	
	}
}

 
 if($api!='') { 
	header('Content-type: application/json');
	require_once("JSON.php");
	/*xml*/
	$code = isquotetion($api,$forcedtrans);
	global $tipps;
	$tipps[] = 'API';
	if(is_array($code)) {
		$return = array_merge($return,quotetion(array('verses','array',"code"=>$code)));
		$return['error'] = array_merge($errors,$return['error']);
		$reftrans = $code['reftrans'];
	} else {
		$errors[] = 'Nincs megfelelő vers';
		$return['error'] = $errors;
		if(isset($forcedtrans)) $reftrans = $forcedtrans; else $reftrans = -1;
	}
	echo json_encode($return);
	if(!isset($code['tag'])) insert_stat($api,$reftrans,-1);
	else insert_stat($api,$reftrans,0);
	
	exit;
}
 
?>