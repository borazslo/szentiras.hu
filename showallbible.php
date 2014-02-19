<?php
$script = explode("&",$_SERVER['REQUEST_URI']);

	$meta = '<meta property="og:description" content="Szent István Társulati Biblia, Káldi-Neovulgáta, Károli Gáspár revideált fordítása és a Magyar Bibliatársulat újfordítású Biblia. Keresés, rövidítés, olvasás, megosztás.">';
	$pagetitle = "Minden fordítás | Szentírás"; 
	$title = 'Biblia fordítások';
    
	$content .= "<br>".showbible(listbible());
	
	
?>