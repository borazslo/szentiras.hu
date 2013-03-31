<?php


 $title = 'Fejlesztőknek';
 
//$content = $api;

$content  .= <<<EOD
<br><p class="alcim">Widgetek, pluginek, stb.</p>
<p>Egyelőre semmilyen widget/plugin sem áll rendelkezésre. Ha bármilyen ötlete vagy igénye van szívesen segítünk a kifejlesztésében. 
Létező munkáját kérjük ossza meg velünk, hogy másoknak is hasznára lehessen.</p>
<p>Kellő jelentkező/igénylő/használó esetén kifejleszthetünk egy <a href="http://reftagger.com/">reftagger.com</a> szerű eszközt is. Kérjük jelezze, ha használna ilyet.</p>

<br><p class="alcim">API</p>
<p>Az egyes szentírási szakaszokat távolról is el lehet érni <a href="http://hu.wikipedia.org/wiki/JSON">JSON</a> formátumban a <a href="http://szentiras.hu/API/Jn1,1-10">szentiras.hu/API/<i>{szentírási hely}</i></a> hivatkozás bekérésével.</p>
<div style="font-size: 10px;
line-height: 13px;
background-color: rgba(0,0,0,0.1);">
{<br>
&nbsp;&nbsp;&nbsp;"code":"Jn 1,1-10",<br>
&nbsp;&nbsp;&nbsp;"verses":<br>
&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"did":"30547",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"reftrans":"1",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"abbook":"Jn",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"numch":"1",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"numv":"1"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"verse":"Kezdetben volt az Ige, az Ige Istenn\u00e9l volt, \u00e9s Isten volt az Ige,",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"title":"El\u0151sz\u00f3"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;},<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;]<br>
}<br>
</div>
<br><p>Ha a szentírási helyet nem sikerült feloldani, akkor üres "verses"-el tér vissza:</p>
<div style="font-size: 10px;
line-height: 13px;
background-color: rgba(0,0,0,0.1);">
{<br>
&nbsp;&nbsp;&nbsp;"code":"Jn 1,1-10",<br>
&nbsp;&nbsp;&nbsp;"verses":[]<br>
}</div>
<br><p>Kérésre elérhetővé tesszük XML vagy bármely más közismert formátumban is.</p>


EOD;

$return = array() ;
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
		require_once("JSON.php");
		$errors[] = 'Nem létező fordítás';
		echo json_encode(array('error'=>$errors));
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