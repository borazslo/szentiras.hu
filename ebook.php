<?php
if(!isset($reftrans)) $reftrans = $_REQUEST['reftrans'];
if(!isset($abbook)) $abbook = $_REQUEST['abbook'];
if(!isset($type)) $type = 'epub';

if (!(empty($reftrans) or empty($abbook))) {
	$transname = gettransname($db,$reftrans,'true');

	$pagetitle = $abbook." ".$numch." (".$transname.") ".$type." | Szentírás"; 
	$title = $abbook." ".$numch." (".$transname.") .".$type; 

	foreach($translations as $tdtrans) { 
		if($tdtrans['id'] == $reftrans) $trans = $tdtrans;}
	foreach($books as $bk) {
		if($bk['trans'] == $reftrans AND $bk['abbrev'] == $abbook) $book = $bk;	}
	
	$filename = $transname.'_'.$abbook."_".date('Y-m-d');
	$filexists = '/var/www/szentiras.hu/ebook/'.$filename.'.'.$type;
	if(file_exists($filexists)) {
		$tipps[] = 'EPUB';
		insert_stat('feladat:'.$abbook.'|'.$type, $reftrans, 0);
		getdownload($filename.'.'.$type);
		exit;
	}
	
//epub

$content_start =
"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
. "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
. "<head>"
. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
. "<title>".$transname." - ".$book['name']."</title>\n"
. "</head>\n"
. "<body>\n";

$bookEnd = "</body>\n</html>\n";

include_once("include/epub/EPub.php");
$epub = new EPub();

$description = "A Szentírásból <i>".$book['name']."</i> a <i>".$trans['publisher']."</i> fordításban a http://szentiras.hu oldalról letöltve.";
$copyright = "A kiadó csak szentiras.hu oldalon való szövegközléshez járult hozzá!";

// Title and Identifier are mandatory!
$epub->setTitle($trans['name']." - ".$book['name']);
$epub->setIdentifier("http://szentiras.hu/".$transname."/".$abbook, EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
$epub->setLanguage("hu"); 
$epub->setDescription($description);
$epub->setAuthor($trans['publisher'],$transname); 
$epub->setPublisher($trans['publisher'], $trans['publisherurl']); // I hope this is a non existant address :) 
$epub->setDate(time()); // Strictly not needed as the book date defaults to time().
$epub->setRights($copyright); 
$epub->setSourceURL("http://szentiras.hu/".$transname."/".$abbook);

$cover = $content_start . "<h1>Szentírás - ".$book['name']."</h1>\n<h2>".$trans['publisher']." fordításban</h2>
	<br />*<br />
	$description<br />
	$copyright<br />
	".date('Y.m.d')."\n" . $bookEnd;
$epub->addChapter("Notices", "Cover.html", $cover);

$rs = listbook($db, $reftrans, $abbook);
   if ($rs->GetNumOfRows() > 0) {
        $rs->firstRow();
	 do {
            $chaptertitle =  $rs->fields["chapter"] . ". fejezet";
        
			list($res1, $res2, $res3, $res4) = listchapter($db, $reftrans, $abbook, $rs->fields["chapter"]);

			if ($res4->GetNumOfRows() > 0) {
			$return  =  '<p>';
        
        $res4->firstRow();
	 do {
            if (strlen(trim($res4->fields["title"]))>0) {
				$strongs = preg_split('/\<br\>/',$res4->fields["title"]);
				foreach($strongs as $key => $strong)
						if($strong != '') {
							if($key == 0) $return .= "</p>";
							$return .= "<strong>" . preg_replace('/\<br\>/',' ',$strong). "</strong><p>";
						}
            }
            $return .= "&nbsp;<sup>" . $res4->fields["numv"] . "</sup>";
            
			//if($reftrans == 3) $res4->fields["verse"] = preg_replace_callback("/{(.*)}/",'replace_hivatkozas',$res4->fields["verse"]);
			
			$res4->fields["verse"] = preg_replace('/\<br\>/','</p><p>',$res4->fields["verse"]);
			
			$res4->fields["verse"] = preg_replace('/>>>/','>»',$res4->fields["verse"]);
			$res4->fields["verse"] = preg_replace('/>>/','»',$res4->fields["verse"]);
			$res4->fields["verse"] = preg_replace('/<<</','«<',$res4->fields["verse"]);
			$res4->fields["verse"] = preg_replace('/<</','«',$res4->fields["verse"]);
			
			$res4->fields["verse"] = preg_replace('/ "/',' „',$res4->fields["verse"]);
			$res4->fields["verse"] = preg_replace('/"( |,|\.|$)/','”$1',$res4->fields["verse"]);
			
			
			$return .= strip_tags($res4->fields["verse"],'<p>');
			
			$return = strip_tags($return,'<sup><strong><br><p>');
            
            $res4->nextRow();
	 } while (!$res4->EOF);		   
			
			$chaptercontent = $content_start . "<h1>".$chaptertitle."</h1>" . $return ."</p>".$bookEnd;
			
			//echo 'FEJLŐD'.$return."<br>";
			
			$epub->addChapter($rs->fields['chapter'].". fejezet", "Chapter".sprintf('%08d', $rs->fields['chapter']).".html", $chaptercontent);
	 
	 $rs->nextRow();
	 }
	 } while (!$rs->EOF);
    //$return .= showbookabbrevlist($db,$reftrans,$abbook);
    }
	
$epub->finalize(); // Finalize the book, and build the archive.

// This is not really a part of the EPub class, but IF you have errors and want to know about them,
//  they would have been written to the output buffer, preventing the book from being sent.
//  This behaviour is desired as the book will then most likely be corrupt.
//  However you might want to dump the output to a log, this example section can do that:
/*
if (ob_get_contents() !== false && ob_get_contents() != '') {
	$f = fopen ('./ebook/log.txt', 'a') or die("Unable to open log.txt.");
	fwrite($f, "\r\n" . date("D, d M Y H:i:s T") . ": Error in " . __FILE__ . ": \r\n");
	fwrite($f, ob_get_contents() . "\r\n");
	fclose($f);
}
*/

// Save book as a file relative to your script (for local ePub generation)
// Notice that the extions .epub will be added by the script.
// The second parameter is a directory name which is '.' by default. Don't use trailing slash!

$epub->saveBook($filename, './ebook');

$tipps[] = 'EPUB';
insert_stat('feladat:'.$abbook.'|'.$type, $reftrans, 0);



if($type == 'epub') {
	// Send the book to the client. ".epub" will be appended if missing.
	$zipData = $epub->sendBook('Szentírás ('.$trans['abbrev'].') - '.$abbook);
} elseif($type == 'mobi') {
	exec('/var/www/kindlegen /var/www/szentiras.hu/ebook/'.$filename.'.epub -c2 -o '.$filename.'.mobi',$output,$return_var);
	//print_R($return_var);	echo "XXX"; 	print_R($output);
	getdownload($filename.'.mobi');
	//echo $redirect;
	
}	
	
}

function getdownload($filename,$path = '') {
	if($path == '') $path = '/var/www/szentiras.hu/ebook/';
	
	header("Content-Disposition: attachment; filename=" . urlencode($filename));    
	header("Content-Type: application/force-download");
	header("Content-Type: application/download");
	$fp = fopen($path.$filename, "r"); 
	while (!feof($fp))
	{
		echo fread($fp, 65536); 
		flush(); // this is essential for large downloads
	}  
	fclose($fp); 
	

}


?>