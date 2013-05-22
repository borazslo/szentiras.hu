<?php
	header('Content-type: text/html; charset=utf-8'); 
	
	$json = file_get_contents('http://szentiras.hu/API/?feladat=idezet&hivatkozas=1Kor2,10-14');
    $data = json_decode($json, TRUE);
	
	if($data['valasz'] == '') echo '<strong>Hiba történt:</strong> '.$data['hiba'];
	else {
		echo "<strong>1Kor2,10-14:</strong><br>\n";
		foreach($data['valasz']['versek'] as $vers) {
			echo $vers['szoveg']." ";
		}
	}
	
	echo"<br><br>";
	
    $json = file_get_contents('http://szentiras.hu/API/?feladat=forditasok&hivatkozas=10100300100');
    $data = json_decode($json, TRUE);
	if($data['valasz'] == '') echo '<strong>Hiba történt:</strong> '.$data['hiba'];
	else {
		echo "<strong>Ter 3,1:</strong><br>\n";
		foreach($data['valasz'] as $valasz) {
			echo "<strong>".$valasz['forditas']['nev'].":</strong> ".$valasz['szoveg']."<br>\n";
		}
	}
	
    
?>