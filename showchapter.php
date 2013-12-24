<?php
//if (!(empty($reftrans) or empty($abbook) or empty($numch))) {
if(!(empty($transid) or empty($bookid))) {
	$reftrans = $transid;
	$abbook = $GLOBALS['tdbook'][$transid][$bookid]['abbrev'];
	$numch = $chapter;
 
	$pagetitle = $abbook." ".$numch." (".gettransname($reftrans,'true').") | Szentírás"; 
	
	$verses = listchapter($reftrans, $abbook, $numch);
    $content .= showchapter($reftrans, $abbook, $numch, $verses);
  
	list($res5,$res6,$res7) = listcomm($verses,$reftrans);
    
    $comments .= showcomms($res5,$reftrans,$res6,$res7);
	
 
	global $content;
	$description = preg_replace('/( [\d]+?)|([\d]+?)|(")/','',strip_tags($content));
	if (strlen($description) > 300) {
		$stringCut = substr($description, 0, 300);
		$description = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
	}
	if (strlen($description) > 90) {
				$stringCut = substr($description, 0, 90);
				$datatext = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
			}
	
	$meta = '<meta property="og:description" content="'.$description.'">'."\n";
	global $texttosearch;
	$meta .= '<meta property="og:url" content="'.BASE.urlencode(preg_replace('/ /i','',$bookabbrevs[$reftrans][$abbook]['abbrev']." ".$numch)).'/" />'."\n";
		
	global $texttosearch;
	$meta .= '<meta property="og:title" content="Idézet a Szentírásból: '.$texttosearch.'">'."\n";
	
	global $share;
	
	$share .= '
		<div id="facebook">
			<a expr:share_url="data:post.url" href="http://www.facebook.com/sharer.php?" name="fb_share" rel="nofollow" type="button">Megosztom</a>
			<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"/></script>
		</div>
		 ';
	
	$share .= '<div id="twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-related="jasoncosta" data-lang="hu"  data-count="none" data-hashtags="Biblia" data-url="'.BASE.urlencode(preg_replace('/ /','',$bookabbrevs[$reftrans][$abbook]['abbrev']." ".$numch)).'/" data-text="'.$datatext.'">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
 
 
	
	
	
 
 }

?>