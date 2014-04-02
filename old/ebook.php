<?php
require_once('include/php-ga-1.1.1/src/autoload.php');
use UnitedPrototype\GoogleAnalytics;

if(!isset($reftrans)) $reftrans = $_REQUEST['reftrans'];
if(!isset($bookid)) $bookid = $_REQUEST['bookid'];
if(!isset($type)) $type = 'epub';

if(!isset($bookid) or $bookid == '') { $bookid = false; };
if(!isset($reftrans) or $reftrans == '') { $reftrans = 1 ;};

if($bookid != false) $abbook = $GLOBALS['tdbook'][$reftrans][$bookid]['abbrev'];

$transname = $GLOBALS['tdtrans'][$reftrans]['abbrev'];
foreach($translations as $tdtrans) { 
		if($tdtrans['id'] == $reftrans) $trans = $tdtrans;}


$ebookfolder = 'tmp';
		
if($bookid != false) $filename = "Szentiras_".$transname.'_'.$abbook."_".date('Y-m');		
else $filename = "Szentiras_".$transname."_".date('Y-m');		
/*
	$pagetitle = $abbook." ".$numch." (".$transname.") ".$type." | Szentírás"; 
	$title = $abbook." ".$numch." (".$transname.") .".$type; 
*/

	$filexists = FILE.$ebookfolder.'/'.$filename.'.'.$type;
	if(file_exists($filexists)) {
		$tipps[] = 'EPUB';
		$count = 1;
		insert_stat($transname." ".$abbook, $reftrans, array('tipus'=>$type,'uj'=>'nem'), 'ebook');
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
. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n";
if($bookid != false) $content_start .= "<title>".$transname." - ".$GLOBALS['tdbook'][$reftrans][$bookid]['name']."</title>\n";
else $content_start .= "<title>".$transname."</title>\n";
$content_start .= "</head>\n"
. "<body>\n";

$bookEnd = "</body>\n</html>\n";

include_once("include/epub/EPub.php");
$epub = new EPub();

//if($bookid != false) $description = "A Szentírásból <i>".$GLOBALS['tdbook'][$reftrans][$bookid]['name']."</i> a <i>".$trans['publisher']."</i> fordításban a http://szentiras.hu oldalról letöltve.";
//else $description = "A http://szentiras.hu oldalról letöltve.";
$description = '';

$copyright = "A kiadó csak szentiras.hu oldalon való szövegközléshez járult hozzá!";

// Title and Identifier are mandatory!
if($bookid != false) $epub->setTitle($trans['name']." - ".$GLOBALS['tdbook'][$reftrans][$bookid]['name']);
else $epub->setTitle($trans['name']);
$epub->setIdentifier("http://szentiras.hu/".$transname."/".$abbook, EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
$epub->setLanguage("hu"); 
$epub->setDescription($description);
$epub->setAuthor($trans['publisher'],$transname); 
$epub->setPublisher($trans['publisher'], $trans['publisherurl']); // I hope this is a non existant address :) 
$epub->setDate(time()); // Strictly not needed as the book date defaults to time().
$epub->setRights($copyright); 
$epub->setSourceURL("http://szentiras.hu/".$transname."/".$abbook);

$epub->addDublinCoreMetadata(DublinCore::CONTRIBUTOR, "PHP");
$epub->setSubject("Szentírás");
$epub->setSubject("Biblia");


//$cssData = "body {\n  margin-left: .5em;\n  margin-right: .5em;\n  text-align: justify;\n}\n\np {\n  font-family: serif;\n  font-size: 10pt;\n  text-align: justify;\n  text-indent: 1em;\n  margin-top: 0px;\n  margin-bottom: 1ex;\n}\n\nh1, h2 {\n  font-family: sans-serif;\n  font-style: italic;\n  text-align: center;\n  background-color: #6b879c;\n  color: white;\n  width: 100%;\n}\n\nh1 {\n    margin-bottom: 2px;\n}\n\nh2 {\n    margin-top: -2px;\n    margin-bottom: 2px;\n}\n";
$cssData = " .kiscim { \n font-weight: bold; \n} \n";
$epub->addCSSFile("styles.css", "css1", $cssData);


//$book->setCoverImage("Cover.jpg", file_get_contents("demo/cover-image.jpg"), "image/jpeg");

$cover = $content_start . "<h1>Szentírás</h1>\n<h2>".$trans['publisher']." fordításában</h2>
	<br />&nbsp;<br />
	$description<br />
	".$trans['reference']."<br/>
	<br/><center>$copyright<br />
	<br/>".date('Y.m.d')."\n</center>" . $bookEnd;
$epub->addChapter("Bevezető", "Cover.html", $cover);
$epub->buildTOC(NULL, "toc", "Tartalomjegyzék", TRUE, TRUE);

foreach($GLOBALS['tdbook'][$reftrans] as $book) {
	if($bookid == false OR $bookid == $book['id']) {
	//$bookid = $book['id'];

	$booktitle =  $GLOBALS['tdbook'][$reftrans][$book['id']]['name'];	 
	$bookcontent = $content_start . "<h1>".$booktitle."</h1>" . "" .$bookEnd;
	$epub->addChapter($booktitle, "Szentiras".$book['id'].".html", $bookcontent);

	$chapters = listbook($reftrans, $GLOBALS['tdbook'][$reftrans][$book['id']]['abbrev']);
	$epub->subLevel();
	foreach($chapters as $chapter) {     
		$chaptertitle =  $chapter->chapter . ". fejezet";	 
		$content = '';
		$verses = listchapter($reftrans, $GLOBALS['tdbook'][$reftrans][$book['id']]['abbrev'], $chapter->chapter);	 
		foreach($verses as $vers)
			$content .= showverse($vers)."\n";
	    $content = preg_replace('/<br>/i','<br/>',$content);
		$chaptercontent = $content_start . "<h2>".$chaptertitle."</h2>" . $content .$bookEnd;
		$epub->addChapter($chapter->chapter.". fejezet", "Szentiras".$book['id']."".sprintf('%03d', $chapter->chapter).".html", $chaptercontent);
	}
	$epub->backLevel();
	}
}
$epub->finalize(); // Finalize the book, and build the archive.

// This is not really a part of the EPub class, but IF you have errors and want to know about them,
//  they would have been written to the output buffer, preventing the book from being sent.
//  This behaviour is desired as the book will then most likely be corrupt.
//  However you might want to dump the output to a log, this example section can do that:
/*
if (ob_get_contents() !== false && ob_get_contents() != '') {
	$f = fopen ('./'.$ebookfolder.'/log.txt', 'a') or die("Unable to open log.txt.");
	fwrite($f, "\r\n" . date("D, d M Y H:i:s T") . ": Error in " . __FILE__ . ": \r\n");
	fwrite($f, ob_get_contents() . "\r\n");
	fclose($f);
}
*/

// Save book as a file relative to your script (for local ePub generation)
// Notice that the extions .epub will be added by the script.
// The second parameter is a directory name which is '.' by default. Don't use trailing slash!

$epub->saveBook($filename, './'.$ebookfolder);

$tipps[] = 'EPUB';
$count = 1;
insert_stat($transname." ".$abbook, $reftrans, array('tipus'=>$type,'uj'=>'igen'), 'ebook');


// Initilize GA Tracker
$tracker = new GoogleAnalytics\Tracker('UA-36302080-1', 'szentiras.hu');

// Assemble Visitor information
// (could also get unserialized from database)
$visitor = new GoogleAnalytics\Visitor();

// Assemble Session information
// (could also get unserialized from PHP session)
$session = new GoogleAnalytics\Session();

/* Assemble Page information
$page = new GoogleAnalytics\Page('/'.$type.'/'.$trans['abbrev'].'/'.$abbook);
$page->setTitle('Szentírás ('.$trans['abbrev'].') - '.$abbook);*/

$event = new GoogleAnalytics\Event('Download',$type,'/'.$type.'/'.$trans['abbrev'].'/'.$abbook);

// Track page view
$tracker->trackEvent($event, $session, $visitor);
/* */

if($type == 'epub') {
	// Send the book to the client. ".epub" will be appended if missing.
	if($bookid != false ) $zipData = $epub->sendBook('Szentírás ('.$trans['abbrev'].') - '.$abbook);
	else $zipData = $epub->sendBook('Szentírás - '.$trans['abbrev'].'');
} elseif($type == 'mobi') {
	exec('/var/www/kindlegen /var/www/szentiras.hu/'.$ebookfolder.'/'.$filename.'.epub -c2 -o '.$filename.'.mobi',$output,$return_var);
	//print_R($return_var);	echo "XXX"; 	print_R($output);
	getdownload($filename.'.mobi');
	//echo $redirect;
	
}	
	




?>