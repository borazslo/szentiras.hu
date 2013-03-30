<?php

$script = explode("&",$REQUEST_URI);
	$meta = '<meta property="og:description" content="Szent István Társulati Biblia, Káldi-Neovulgáta és a Magyar Bibliatársulat újfordítású Biblia. Keresés, rövidítés, olvasás, megosztás.">';
	$pagetitle = "Fordítások | Szentírás"; 
	$title = 'Bibliák';
	$content .= showbible($db,listbible($db));

?>