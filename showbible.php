<?php
$script = explode("&",$_SERVER['REQUEST_URI']);
	$meta = '<meta property="og:description" content="Katolikus fordítások: Szent István Társulati Biblia, Káldi-Neovulgáta. Keresés, rövidítés, olvasás, megosztás.">';
	$pagetitle = "Fordítások | Szentírás"; 
	$title = 'Katolikus Biblia fordítások';
        
	$content .= "<br>".showbible(listbible('katolikus'));
	
    $content .= '<br><h4><a href="http://www.bences.hu/igenaptar/'.date('Ym').'.html">Napi olvasmányok</a></h4>';
	$content .= igenaptar();

	$content .= "<span class='alcim'><a href='".BASE."forditasok'>További fordítások</a></span>";
	$content .= "<br>".showbible(listbible('protestáns'),'simple');	
	//$hir = 'ÚJDONSÁG: a keresét le lehet szűkíteni egy-egy könyvre vagy az Ószövetségre/Újszövetségre a keresés végére írt <strong>in:<i>könyv rövidítés</i></strong> formával! Például: <A href="http://szentiras.hu/SZIT/%C3%B6r%C3%B6m%20in:Lk">öröm in:Lk</a>';
?>