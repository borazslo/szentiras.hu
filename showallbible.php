<?php
$script = explode("&",$_SERVER['REQUEST_URI']);
	$meta = '<meta property="og:description" content="Szent István Társulati Biblia, Káldi-Neovulgáta, Károli Gáspár revideált fordítása és a Magyar Bibliatársulat újfordítású Biblia. Keresés, rövidítés, olvasás, megosztás.">';
	$pagetitle = "Minden fordítás | Szentírás"; 
	$title = 'Biblia fordítások';
    
	$content .= "<br>".showbible(listbible());
	
	//$content .= '<br><h4><a href="http://www.bences.hu/igenaptar/'.date('Ym').'.html">Napi olvasmányok</a></h4>';
	//$content .= igenaptar();
	
	//$hir = 'ÚJDONSÁG: a keresét le lehet szűkíteni egy-egy könyvre vagy az Ószövetségre/Újszövetségre a keresés végére írt <strong>in:<i>könyv rövidítés</i></strong> formával! Például: <A href="http://szentiras.hu/SZIT/%C3%B6r%C3%B6m%20in:Lk">öröm in:Lk</a>';
?>