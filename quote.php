<?php

/*
 RÉGI regexp
 
// könyv
(...)
// fejezet
([0-9]{1,3})
(
	(
		// :kezdővers
		((,|:)[0-9]{1,2}[a-f]{0,1})
		// -befejezővers vagy .másikvers akárhányszor
		((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*
	)
	|
		// -másikfejezet
		(-[0-9]{1,2})
	|
	(
		// :kezdővers-másikfejezet:befejezővers
		(:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2}
	)
){0,1}
(;
	// az előzőek pontosvesszővel elválasztva újra akárhányszor
	([0-9]{1,3}) ((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*) |(-[0-9]{1,2}) |((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}
)*



 ÚJ regexp

Amit elfogad, de a régi nem:		Jn 3-4,5 Zsolt 99-100
Amit nem fogad el, de a régi igen:	Jn 3,4-7-12
Amit egyik se fogad el:				Jn 3,4.5-4,1-6 (ha több fejezetet kérünk, akkor max 1-1 vers a határ)

// könyv
(\d?[^\d\.:]+)
// esetleg egy (kettős)pont
[\.:]?
(
	// fejezet
	[0-9]{1,3}
	(
		(
			(
				// :kezdővers
		  		[,:][0-9]{1,2}[a-f]?
			  	(
		  			(
	  					// -befejezővers?
	  					(-[0-9]{1,2}[a-f]?)?
	  					// az előző kettő tetszőleges számban ponttal elválasztva
	  					(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*
		  			)
		  			|
		  			(
	  					// -másikfejezet:befejezővers
	  					-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?
		  			)
			  	)
			)
	  		|
		  	(
	  			// -másikfejezet és esetleg :befejezővers
	  			-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?
		  	)
	  	)
		(;
			// az előzőek a fejezettől kezdve tetszőleges számban, pontosvesszővel elválasztva
			[0-9]{1,3}([,:][0-9]{1,2}[a-f]?(((-[0-9]{1,2}[a-f]?)?(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*)|(-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?)))|(-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?)
		)*
	)?
)
*/

/**
 * Feldolgoz egy hivatkozást
 * @param string $hivatkozas Egy hivatkozás, pl. 1Kor 13, 1-13
 * @param string $forditas A használni kívánt fordítás.
 * @return array [<br/>
 * 				'book' 		=> a könyv helyes rövidítése<br/>
 * 				,'bookurl' 	=> a könyv URL-je<br/>
 * 				,'code' 	=> a hivatkozás javított kódja<br/>
 * 				,'reftrans' => a választott fordítás<br/>
 *				, 'tag'	=> [ 'code', 'chapter', 'numv'[] ] <br/>
 * 			]<br/>
 */
function isquotetion($hivatkozas, $forditas) {
	global $db;
	
	// TODO ez csak ideiglenes javítás
	global $reftrans;	
	if (!isset($forditas)) {
		$forditas = $reftrans;
	}
	
	// szóközök és egyebek törlése
	$ref = preg_replace("/\s+/", "", $hivatkozas);
	
	// pl. 83. zsoltár cseréje Zsolt 83-ra
	if (preg_match('/([0-9]{1,3})\.?zsolt[aá]r/i', $ref, $match)) {
		$ref = 'Zsolt' . $match[1];
	}
	
	$regex = '/(\d?[^\d\.:]+)[\.:]?([0-9]{1,3}((([,:][0-9]{1,2}[a-f]?(((-[0-9]{1,2}[a-f]?)?(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*)|(-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?)))|(-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?))(;[0-9]{1,3}([,:][0-9]{1,2}[a-f]?(((-[0-9]{1,2}[a-f]?)?(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*)|(-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?)))|(-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?))*)?)/u';
	
	// ha nem érvényes hivatkozás, tovább nem foglalkozunk vele
	if (!preg_match($regex, $ref, $matches)) {
		return false;
	}
	
	// kikeresi a könyvet (esetleg hibásan is, de a pontos találat előnyt élvez) a választott fordítással
	$q = $db->prepare('SELECT ' . DBPREF . 'tdbook.id, trans, abbrev, url, countch
						FROM ' . DBPREF . 'tdbook
							INNER  JOIN
							(
								SELECT 1 AS pontos, id FROM ' . DBPREF . 'tdbook WHERE abbrev = :abbrev
								UNION
								SELECT 0, id FROM ' . DBPREF . 'tdbook_hibas WHERE abbrev = :abbrev
							) x ON x.id = ' . DBPREF . 'tdbook.id
						AND trans = :trans
						ORDER BY pontos DESC
						LIMIT 1');
	
	if ($q->execute(array (
		':abbrev' => $matches[1],
		':trans' => $forditas 
	))) {
		$book = $q->fetch();
	}
	else {
		// TODO hibakezelés: log?
		return false;
	}
	
	$text = $matches[2];
	
	$quote = array (
		'book' => $book['abbrev'],
		'bookurl' => $book['url'],
		'reftrans' => $forditas,
		'code' => $book['abbrev'] . ' ' . $text  // TODO ez eddig is így volt, de nem jó (a hibás részeket ki kellen hagyni)
	);
	
	$pattern = "/^[0-9]{1,3}$/i";
	if (preg_match($pattern, $text, $match)) {
		$query = "SELECT numv FROM " . DBPREF . "tdverse LEFT JOIN " . DBPREF . "tdbook ON book = " . DBPREF . "tdbook.id AND " . DBPREF . "tdbook.trans = " . DBPREF . "tdverse.trans  WHERE " . DBPREF . "tdverse.trans = " . $quote['reftrans'] . " AND " . DBPREF . "tdbook.abbrev = '" . $quote['book'] . "' AND chapter = " . $match[0] . " ORDER BY ABS(numv) DESC LIMIT 1";
		$numv = db_query($query);
		if (is_array($numv) and $numv[0]['numv'] > 0) {
			/* TODO: vesszőt vagy kettőspontot?? */
			$quote['tag'][1]['code'] = $match[0] . ",1-" . $numv[0]['numv'];
			$quote['tag'][1]['chapter'] = $match[0];
			for($s = 1; $s <= $numv[0]['numv']; $s++)
				$quote['tag'][1]['numv'][] = $s;
		}
	}
	
	$tags = explode(';', $text);
	foreach ( $tags as $key => $tag ) {
		$vesszo = count(explode(',', $tag));
		$kettospont = count(explode(':', $tag));
		if ($vesszo > $kettospont)
			$case = $vesszo;
		else
			$case = $kettospont;
		switch ($case - 1) {
			case 0 :
				preg_match('/^([0-9]{1,3})-([0-9]{1,3})$/', $tag, $tmp);
				if (count($tmp) > 2) {
					for($c = $tmp[1]; $c <= $tmp[2]; $c++) {
						$query = "SELECT numv FROM " . DBPREF . "tdverse LEFT JOIN " . DBPREF . "tdbook ON book = " . DBPREF . "tdbook.id AND " . DBPREF . "tdbook.trans = " . DBPREF . "tdverse.trans WHERE " . DBPREF . "tdverse.trans = " . $quote['reftrans'] . " AND " . DBPREF . "tdbook.abbrev = '" . $quote['book'] . "' AND chapter = $c ORDER BY ABS(numv) DESC LIMIT 1";
						$numv = db_query($query);
						if (is_array($numv) and $numv[0]['numv'] > 0) {
							/* TODO: vesszőt vagy kettőspontot?? */
							$quote['tag'][$key * 100 + $c]['code'] = $c . ",1-" . $numv[0]['numv'];
							$quote['tag'][$key * 100 + $c]['chapter'] = $c;
							for($s = 1; $s <= $numv[0]['numv']; $s++)
								$quote['tag'][$key * 100 + $c]['numv'][] = $s;
						}
					}
				}
				break;
			case 2 :
				preg_match('/^([0-9]{1,3})(,|:)([0-9]{1,2})-([0-9]{1,3})(,|:)([0-9]{1,2})$/', $tag, $tmp);
				for($c = $tmp[1]; $c <= $tmp[4]; $c++) {
					$query = "SELECT numv FROM " . DBPREF . "tdverse LEFT JOIN " . DBPREF . "tdbook ON book = " . DBPREF . "tdbook.id AND " . DBPREF . "tdbook.trans = " . DBPREF . "tdverse.trans WHERE " . DBPREF . "tdverse.trans = " . $quote['reftrans'] . " AND " . DBPREF . "tdbook.abbrev = '" . $quote['book'] . "' AND chapter = $c ORDER BY ABS(numv) DESC LIMIT 1";
					$numv = db_query($query);
					if (is_array($numv) and $numv[0]['numv'] > 0) {
						if ($c == $tmp[1])
							$from = $tmp[3];
						else
							$from = 1;
						if ($c == $tmp[4])
							$to = $tmp[6];
						else
							$to = $numv[0]['numv'];
						$quote['tag'][$key * 100 + $c]['code'] = $c . $tmp[2] . $from . "-" . $to;
						$quote['tag'][$key * 100 + $c]['chapter'] = $c;
						for($s = $from; $s <= $to; $s++)
							$quote['tag'][$key * 100 + $c]['numv'][] = $s;
					}
				}
				break;
			case 1 :
				preg_match('/^([0-9]{1,3})(,|:)(.*?)$/', $tag, $tmp);
				$query = "SELECT numv FROM " . DBPREF . "tdverse LEFT JOIN " . DBPREF . "tdbook ON book = " . DBPREF . "tdbook.id AND " . DBPREF . "tdbook.trans = " . DBPREF . "tdverse.trans WHERE " . DBPREF . "tdverse.trans = " . $quote['reftrans'] . " AND " . DBPREF . "tdbook.abbrev = '" . $quote['book'] . "' AND chapter = " . $tmp[1] . " ORDER BY ABS(numv) DESC LIMIT 1";
				$numv = db_query($query);
				if (is_array($numv) and $numv[0]['numv'] > 0) {
					$quote['tag'][$key * 100]['chapter'] = $tmp[1];
					$quote['tag'][$key * 100]['code'] = $tag;
					
					$tmp2 = explode('.', $tmp[3]);
					foreach ( $tmp2 as $vers ) {
						if (preg_match('/^([0-9]{1,2})-([0-9]{1,2})$/', $vers, $tmp3)) {
							for($s = $tmp3[1]; $s <= $tmp3[2]; $s++) {
								if ($s <= $numv[0]['numv'])
									$quote['tag'][$key * 100]['numv'][] = $s;
							}
						}
						else {
							if ($vers <= $numv[0]['numv'])
								$quote['tag'][$key * 100]['numv'][] = $vers;
						}
					}
				}
				break;
		}
	}
	
	return $quote;
}
function print_quotetion($args) {
	global $code;
	global $verses;
	global $error;
	global $query;
	
	$return = false;
	
	if (!is_array($args))
		$args = array (
			$args 
		);
	if (in_array('title', $args))
		$return .= "<p class='cim'>Idézet a szentírásból: $query<p>";
	if (in_array('form', $args))
		$return .= print_form();
	
	if (in_array('verses', $args)) {
		$averses = $verses;
		
		// TODO: fejezetváltásokkor
		$verses = print_verses($verses);
		if ($averses == array ())
			$error[] = 'Nincs megjeleníthető vers!';
		
		$tmpverses = array ();
		$verses = '';
		foreach ( $averses as $v ) {
			global $db;
			$query = "SELECT gepi, " . DBPREF . "tdverse.trans, did, numv, gepi, tip, verse FROM " . DBPREF . "tdverse WHERE gepi = " . $v['gepi'] . " AND trans = " . $v['trans'];
			$stmt = $db->prepare($query);
			$stmt->execute();
			$rs = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_GROUP);
			$rs = array_shift(array_values($rs));
			$tmp = array ();
			foreach ( $rs as $key => $jelenseg ) {
				$tmp[$jelenseg->tip] = $jelenseg;
			}
			$verse = $tmp;
			$verses .= showverse($tmp);
			$tmpverses[] = $tmp;
		}
		
		if (isset($pverses) and $pverses == "<span class='alap'></span>	") {
			$return .= "Nincs találat.";
		}
		else {
			$return .= $verses;
			
			global $meta;
			$description = preg_replace('/( [\d]+?)|([\d]+?)|(")/', '', strip_tags($verses));
			if (strlen($description) > 300) {
				$stringCut = substr($description, 0, 300);
				$description = substr($stringCut, 0, strrpos($stringCut, ' ')) . '...';
			}
			if (strlen($description) > 90) {
				$stringCut = substr($description, 0, 90);
				$datatext = substr($stringCut, 0, strrpos($stringCut, ' ')) . '...';
			}
			if (!isset($datatext))
				$datatext = $description;
			
			$meta = '<meta property="og:description" content="' . $description . '">';
			global $texttosearch;
			
			$url = preg_replace('/ /', '', preg_replace('/^(' . $code['book'] . ')/', $code['bookurl'], $code['code']));
			$meta .= '<meta property="og:url" content="' . BASE . urlencode($url) . '/" />';
			
			global $texttosearch;
			$meta .= '<meta property="og:title" content="Idézet a Szentírásból: ' . $texttosearch . '">';
			
			global $share;
			
			$share .= '
				<div id="facebook">
					<a expr:share_url="data:post.url" href="http://www.facebook.com/sharer.php?" name="fb_share" rel="nofollow" type="button">Share</a>
					<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"/></script>
				</div>
				 ';
			
			$share .= '<div id="twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-related="jasoncosta" data-lang="hu"  data-count="none" data-hashtags="Biblia" data-url="' . BASE . urlencode($url) . '/" data-text="' . $datatext . '">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
			$share .= '<input style="padding:2px;" type="button" onclick="window.prompt(\'Rövid cím, amin elérhető ez az oldal:\',\'' . BASE . urlencode($url) . '\');" value="rövid url">';
		}
	}
	$dids = array ();
	global $db, $reftrans, $comments, $reftrans;
	foreach ( $averses as $verses ) {
		if (isset($verses['did'])) {
			$query = "select * FROM " . DBPREF . "tdverse where did = " . $verses['did'] . " order by did";
			$rs = db_query($query);
			list ( $list1, $list2, $list3 ) = listcomm($rs, $reftrans);
			$rs = $list1;
			foreach ( $rs as $row ) {
				$dids[$row->did] = " did = '" . $row->did . "' ";
			}
		}
		else
			$return .= 'Mégsincs találat. Elnézést.';
	}
	
	$query = "select * FROM " . DBPREF . "tdcomm where (" . implode(' OR ', $dids) . ") order by did";
	$rs = db_query($query);
	$comments .= showcomms($rs, $reftrans, 100, 100);
	
	if (in_array('errors', $args) or is_array($error)) {
		$return .= "<br>" . print_errors($error);
	}
	return $return;
}

function quotetion($argss) {
	$args = array ();
	foreach ( $argss as $k => $v ) {
		if (is_numeric($k)) {
			$args[] = $v;
		}
		else {
			${$k} = $v;
		}
	}
	
	if (!isset($code))
		global $code;
	$kod = $code;
	$code = $code['code'];
	
	global $error;
	global $book;
	global $verses;
	global $reftrans;
	global $query;
	
	if (!in_array('html', $args) and !in_array('array', $args) and !in_array('json', $args) and !in_array('xml', $args))
		$args = array_merge($args, array (
			'html' 
		));
	if (!in_array('title', $args) and !in_array('form', $args) and !in_array('verses', $args))
		$args = array_merge($args, array (
			'title',
			'form',
			'verses' 
		));
	
	$aa = array (
		'html',
		'json',
		'xml',
		'array',
		'title',
		'form',
		'verses',
		'errors' 
	);
	$tmp = array ();
	
	foreach ( $args as $k => $i ) {
		if (!in_array($i, $aa))
			$tmp[] = $i;
	}
	foreach ( $tmp as $t ) {
		if (is_numeric($t))
			$reftrans = $t;
		else
			$code = $query = $t;
	}
	
	$error = array ();
	/* ellenőrzés, hogy semmi spéci karakter ne legyen benne */
	
	$c = 0;
	if (isset($kod['tag'])) {
		foreach ( $kod['tag'] as $tag ) {
			if (isset($tag['numv'])) {
				foreach ( $tag['numv'] as $numv ) {
					$where = array (
						DBPREF . 'tdverse.trans' => $kod['reftrans'],
						DBPREF . 'tdbook.abbrev' => $kod['book'],
						'chapter' => $tag['chapter'],
						'numv' => $numv 
					);
					$w = array ();
					foreach ( $where as $name => $value )
						$w[] = " " . $name . " = '" . $value . "'";
					$query = "SELECT * FROM " . DBPREF . "tdverse LEFT JOIN " . DBPREF . "tdbook ON book = " . DBPREF . "tdbook.id AND " . DBPREF . "tdbook.trans = " . DBPREF . "tdverse.trans WHERE " . implode(' AND ', $w) . " LIMIT 1;";
					$result = db_query($query);
					if (is_array($result))
						$verses2[] = $result[0];
				}
			}
		}
		$verses = $verses2;
		if ($verses == '')
			$verses = array ();
	}
	else
		$verses = array ();
	
	if (in_array('html', $args))
		return print_quotetion($args);
	elseif (in_array('json', $args))
		return json_encode(array (
			'code' => $kod['code'],
			'verses' => $verses 
		)); // ,'errors'=>$errors));
	elseif (in_array('xml', $args))
		return xml_encode(array (
			'verses' => $verses 
		));
	elseif (in_array('array', $args))
		return array (
			'verses' => $verses,
			'error' => $error 
		);
}
function print_errors($error) {
	$return = "<span class=\"alap\"><font color='red'>";
	foreach ( $error as $er )
		$return .= $er . "<br>";
	$return .= "</font></span>";
	return $return;
}
function replace_hivatkozas($m) {
	global $books, $reftrans;
	foreach ( $books as $book )
		if ($book['trans'] == $reftrans)
			$abbrevs[] = $book['abbrev'];
	$verses = preg_replace('/ /', '', $m[1]);
	$pattern = "/(" . implode("|", $abbrevs) . ")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*/i";
	$verses = preg_replace_callback($pattern, "replace_hivatkozas2link", $verses);
	return $verses;
}
function replace_hivatkozas2link($m) {
	global $translations;
	$return = '';
	$quote = isquotetion($m[0],3);
	// print_R($quore);
	if (is_array($quote)) {
		$return = "<a href='" . BASE . $translations[$quote['reftrans']]['abbrev'] . "/" . $quote['code'] . "' class='hivatkozas' style='/*font-size: 21px;*/color: #6274B5;'>[" . $quote['code'] . "]</a>";
	}
	else
		$return = $m[0];
	return $return;
}
function print_verses($verses) {
	global $reftrans;
	
	$return = "<span class='alap'>";
	foreach ( $verses as $k => $verse ) {
		if ($verse != '') {
			
			$verse['verse'] = preg_replace('/ "/', ' „', $verse['verse']);
			$verse['verse'] = preg_replace('/"( |,|\.|$)/', '”$1', $verse['verse']);
			
			// $pattern = "/{(".implode("|",$abbrevs).")([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1}(;([0-9]{1,3})((((,|:)[0-9]{1,2}[a-f]{0,1})((-[0-9]{1,2}[a-f]{0,1})|(\.[0-9]{1,2}[a-f]{0,1}))*)|(-[0-9]{1,2})|((:|,)[0-9]{1,2}-[0-9]{1,2}(:|,)[0-9]{1,2})){0,1})*}/i";
			$pattern = "/{(.*?)}/";
			$verse['verse'] = preg_replace_callback($pattern, 'replace_hivatkozas', $verse['verse']);
			
			if ($verse['title'] != '')
				$return .= "<p class='kiscim'>" . $verse['title'] . "</p>";
			if (array_key_exists('start', $verse) or $k == 0 or $numch != $verse['chapter'])
				$return .= " <strong>" . $verse['chapter'] . "</strong> ";
			$return .= " <sup>" . $verse['numv'] . "</sup>" . $verse['verse'] . " ";
			$numch = $verse['chapter'];
		}
	}
	$return .= "</span>	";
	return $return;
}
function xml_encode($array) {
	/*
	 * $doc = new DOMDocument(); $fragment = $doc->createDocumentFragment(); // adding XML verbatim: $xml = "Test &amp; <b> and encode </b> :)\n"; $fragment->appendXML($xml); // adding text: $text = $xml; $fragment->appendChild($doc->createTextNode($text)); // output the result echo $doc->saveXML($fragment); $xml = 'file'; return $xml;
	 */
}
function insert_stat($texttosearch, $reftrans, $results, $type = '') {
	global $tipps, $original, $translations;
	global $tracker;
	
	global $rows, $page;
	global $count; // $count = count($results);
	if ($type == 'quote')
		$count = $results;
	
	if (!is_array($results))
		$results = array ();
	
	if (isset($_SERVER['HTTP_REFERER']))
		$server = $_SERVER['HTTP_REFERER'];
	else
		$server = '';
	
	$tipp = strip_tags(implode('\n', $tipps));
	db_query("SET NAMES 'utf8'");
	db_query("SET CHARACTER SET 'utf8'");
	global $searchby;
	if ($type == 'quote')
		$notes = 'searchby:quote';
	elseif ($type == 'api') {
		global $apinotes;
		$notes = 'searchby:api' . $apinotes;
	}
	elseif ($type == 'ebook') {
		$notes = 'type:' . $results['tipus'] . '|uj:' . $results['uj'];
	}
	else
		$notes = 'searchby:' . $searchby . '|rows:' . $rows . '|page:' . $page;
	
	$query = "INSERT INTO " . DBPREF . "stats_texttosearch 
		(texttosearch,notes,reftrans,date,result,session,tipp,original,referrer)
		VALUES ('" . $texttosearch . "','" . $notes . "'," . $reftrans . ",'" . date('Y-m-d H:i:s') . "'," . $count . ",'" . session_id() . "','" . $tipp . "','" . $original . "','" . $server . "');";
	db_query($query);
	if ($type == 'quote')
		$stype = 'quote';
	elseif ($type == 'api')
		$stype = 'api';
	elseif ($type == 'ebook')
		$stype = 'ebook';
	else
		$stype = $searchby;
	
	$query = "SELECT texttosearch, searchcount 
			FROM " . DBPREF . "stats_search 
			WHERE 
				texttosearch = '" . $texttosearch . "' 
				AND reftrans = " . $reftrans . " 
				AND rows = '" . $rows . "'
				AND page = '" . $page . "'
				AND searchtype = '" . $stype . "'
			ORDER BY texttosearch DESC LIMIT 1";
	$result = db_query($query, 1);
	
	// echo '--'.$GLOBALS['fullsearch'].'--'.print_r($result,1);
	$searchcount = ($result[0]['searchcount'] + 1);
	if ($searchcount > 1)
		$resultarray = $results;
	else
		$resultarray = array ();
	
	if (is_array($result)) {
		if ($GLOBALS['fullsearch'] == 1) {
		}
		else {
		}
		;
		$query = "UPDATE " . DBPREF . "stats_search 
					SET 
						searchcount = " . $searchcount . " ,						
						resultarray = '" . serialize($resultarray) . "', 
						resultupdated = '" . date('Y-m-d H:i:s') . "',
						resultcount = " . $count . "
					WHERE 
						texttosearch = '" . $texttosearch . "' 
						AND reftrans = " . $reftrans . "
						AND rows = '" . $rows . "'
						AND page = '" . $page . "'
						AND searchtype = '" . $stype . "'						
						;";
		db_query($query, 1);
	}
	else {
		if ($GLOBALS['fullsearch'] == 1) {
		}
		else {
		}
		;
		$query = "INSERT INTO " . DBPREF . "stats_search 
					(texttosearch,reftrans,searchcount,resultcount,resultarray,resultupdated,rows,page,searchtype) 
				VALUES ('" . $texttosearch . "'," . $reftrans . ",1," . $count . ",'" . serialize($resultarray) . "','" . date('Y-m-d H:i:s') . "','" . $rows . "','" . $page . "','" . $stype . "');";
		db_query($query);
	}
}
?>
