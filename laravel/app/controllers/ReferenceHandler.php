<?php

/**
 * Utility methods to process bible references.
 *
 * @author berti
 * 
 */
class ReferenceHandler {
    
 /**
 * Return a normalized standard version of a bible reference.
 * @param string $reference E.g. '1Kor 13, 1-13'
 * @param string $translation The translation to provide the normalization according to.
 * @return array [<br/>
 * 				'book' 		=> correct abbreviation of book<br/>
 * 				,'bookurl' 	=> book url<br/>
 * 				,'code' 	=> the normalized reference<br/>
 * 				,'reftrans' => the chosen translation<br/>
 *				, 'tag'	=> [ 'code', 'chapter', 'numv'[] ] <br/>
 * 			]<br/>
 */
public function normalizeReference($reference, $translationId = 3) {
	$ref = preg_replace("/\s+/", "", $reference);	
	// pl. 83. zsoltár cseréje Zsolt 83-ra
	if (preg_match('/([0-9]{1,3})\.?zsolt[aá]r/i', $ref, $match)) {
		$ref = 'Zsolt '.$match[1];
	}	
	$regex = '/(\d?[^\d\.:]+)[\.:]?([0-9]{1,3}((([,:][0-9]{1,2}[a-f]?(((-[0-9]{1,2}[a-f]?)?(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*)|(-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?)))|(-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?))(;[0-9]{1,3}([,:][0-9]{1,2}[a-f]?(((-[0-9]{1,2}[a-f]?)?(\.[0-9]{1,2}[a-f]?(-[0-9]{1,2}[a-f]?)?)*)|(-[0-9]{1,3}[,:][0-9]{1,2}[a-f]?)))|(-[0-9]{1,3}([,:][0-9]{1,2}[a-f]?)?))*)?)/u';	
	// ha nem érvényes hivatkozás, tovább nem foglalkozunk vele
	if (!preg_match($regex, $ref, $matches)) {
		return false;
	}        
        $abbrev = $matches[1];
	$numbers = $matches[2];
        
        $book = Book::
                    where('abbrev', $abbrev)
                    ->where('trans', $translationId)
                    ->first();
        if (!$book) {
            $book = Book::
                    whereHas('abbrevs', function($q) use ($abbrev) {
                        $q->where('abbrev',$abbrev);
                    })->where('trans', $translationId)
                    ->first();
        }
        
        if (!$book) {
            $quote = array(
                'book' => $abbrev,
                'bookurl' => '',
                'code' => "{$abbrev} {$numbers}",
                'url' => '',
                'reftrans' => $translationId
            );        
        } else {
            $quote = array (
            'book' => $book->abbrev,
            'bookurl' => $book->url,
            'reftrans' => $translationId,
            'code' => "{$book->abbrev} {$numbers}",  // TODO ez eddig is így volt, de nem jó (a hibás részeket ki kellen hagyni)
            'url' => "{$book->url}{$numbers}"
        );            

        
	
        // only one chapter number
	$pattern = "/^[0-9]+$/";
	if (preg_match($pattern, $numbers, $match)) {
            $chapter = $match[0];
            $lastVerseNum = 
                    Verse::whereHas('book', function ($q) use ($book) {
                        $q->where('trans', $book->trans);
                    })
                    ->where('chapter', $chapter)->orderBy('numv', 'desc')->first();
            if ($lastVerseNum && $lastVerseNum > 0) {
                $quote['tag'][1]['code'] = "{$chapter},1-{$lastVerseNum}";
                $quote['tag'][1]['chapter'] = $chapter;
                for($s = 1; $s <= $lastVerseNum; $s++) {
                    $quote['tag'][1]['numv'][] = $s;
                }
            }
	}
	
	$tags = explode(';', $numbers);
	foreach ($tags as $key => $tag) {
            $commaCount = count(explode(',', $tag))-1;
            $colonCount = count(explode(':', $tag))-1;
            $case = $commaCount > $colonCount ? $commaCount : $colonCount;
            switch ($case) {
                case 0 :
                    preg_match('/^([0-9]{1,3})-([0-9]{1,3})$/', $tag, $tmp);
                    if (count($tmp) > 2) {
                        for ($c = $tmp[1]; $c <= $tmp[2]; $c++) {
                            $lastVerseNum = Verse::whereHas('book', function ($q) use ($book) {
                                                $q->where('trans', $book->trans);
                                            })
                                            ->where('chapter', $c)->orderBy('numv', 'desc')->first();

                            if ($lastVerseNum && $lastVerseNum > 0) {
                                $quote['tag'][$key * 100 + $c]['code'] ="{$c},1-{$lastVerseNum}";
                                $quote['tag'][$key * 100 + $c]['chapter'] = $c;
                                for($s = 1; $s <= $lastVerseNum; $s++)
                                    $quote['tag'][$key * 100 + $c]['numv'][] = $s;
                            }
			}
                    }
                    break;
                case 2 :
                    preg_match('/^([0-9]{1,3})(,|:)([0-9]{1,2})-([0-9]{1,3})(,|:)([0-9]{1,2})$/', $tag, $tmp);
                    for ($c = $tmp[1]; $c <= $tmp[4]; $c++) {
                            $lastVerseNum = Verse::whereHas('book', function ($q) use ($book) {
                                                $q->where('trans', $book->trans);
                                            })
                                            ->where('chapter', $c)->orderBy('numv', 'desc')->first();
                            if ($lastVerseNum && $lastVerseNum > 0) {
                            if ($c == $tmp[1])
                                $from = $tmp[3];
                            else
                                $from = 1;
                            if ($c == $tmp[4])
                                $to = $tmp[6];
                            else
                                $to = $lastVerseNum;
                            $quote['tag'][$key * 100 + $c]['code'] = "{$c}{$tmp[2]}{$from}-{$to}";
                            $quote['tag'][$key * 100 + $c]['chapter'] = $c;
                            for($s = $from; $s <= $to; $s++) {
                                    $quote['tag'][$key * 100 + $c]['numv'][] = $s;
                            }
                            
                        }
                    }
                    break;
                case 1 :
                    preg_match('/^([0-9]{1,3})(,|:)(.*?)$/', $tag, $tmp);
                    $c = $tmp[1];
                    $lastVerseNum = Verse::whereHas('book', function ($q) use ($book) {
                                        $q->where('trans', $book->trans);
                                    })
                                    ->where('chapter', $c)->orderBy('numv', 'desc')->first();
                    if ($lastVerseNum && $lastVerseNum > 0) {
                        $quote['tag'][$key * 100]['chapter'] = $c;
                        $quote['tag'][$key * 100]['code'] = $tag;

                        $tmp2 = explode('.', $tmp[3]);
                        foreach ($tmp2 as $vers) {
                            if (preg_match('/^([0-9]{1,2})-([0-9]{1,2})$/', $vers, $tmp3)) {
                                for ($s = $tmp3[1]; $s <= $tmp3[2]; $s++) {
                                    if ($s <= $lastVerseNum)
                                        $quote['tag'][$key * 100]['numv'][] = $s;
                                }
                            }
                            else
                            if ($vers <= $lastVerseNum) {
                                $quote['tag'][$key * 100]['numv'][] = $vers;
                            }
                        }
                    }
                    break;
            }
        }
        
                            }

        return $quote;
    }

}
