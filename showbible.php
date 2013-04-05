<?php

$script = explode("&",$REQUEST_URI);
	$meta = '<meta property="og:description" content="Szent István Társulati Biblia, Káldi-Neovulgáta, Károli Gáspár revideált fordítása és a Magyar Bibliatársulat újfordítású Biblia. Keresés, rövidítés, olvasás, megosztás.">';
	$pagetitle = "Fordítások | Szentírás"; 
	$title = 'Bibliák';
	$content .= "<br>".showbible($db,listbible($db));
	
	$content .= '<br><h4><a href="http://www.bences.hu/igenaptar/'.date('Ym').'.html">Napi olvasmányok</a></h4>';
	$content .= igenaptar();
?>