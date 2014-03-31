<?php

/**
 * Class CanonicalReference to represent a unique reference to some Bible verses.
 * Examples of possible formats:
 * - 1Kor - full book
 * - 1Kor 13 - a full chapter
 * - 1Kor 13,1 - a verse
 * - 1Kor 13,1-10 - a verse range
 * - 1Kor 13,1.2-10.24-30 - multiple ranges
 * - 1Kor 13,1a-3.5b-14 - multiple ranges with verse parts
 * - 1Kor 13;25 - multiple chapters
 * - 1Kor 13,1a-3.5b-14;14,23.24-26 - multiple chapters
 * - 1Kor; Jn; 2Fil - multiple books, note the space
 * - 1Kor 13,1a-3.5b-14.6a;14,23; Jn 14,22-39 - everything combined
 * So generally we have the following grammar here:
 * CanonicalReference = BookReference ("; " BookReference)*
 * BookReference = BookId (" " ChapterReference)
 * ChapterReference = ChapterId ("," VerseReference)? (";" ChapterReference)*
 * VerseReference = VerseRange ("." VerseRange)*
 * VerseRange = VerseId ("-" VerseId)?
 * VerseId = [0-9]+[a-z]?
 * BookId = [0-9]?Alpha+
 * ChapterId = [0-9]+
 *
 * So as an example:
 * - 1Kor 13,1a-3.5b-14.6a;14,23; Jn 14,22-39
 * BookReference
 *  BookId("1Kor")
 *  ChapterReference
 *   ChapterId("13")
 *   VerseReference
 *     VerseRange
 *       VerseId("1a")
 *       VerseId("3")
 *     VerseRange
 *       VerseId("5b")
 *       VerseId("14")
 *     VerseRange
 *       VerseId("6a")
 *  ChapterReference
 *   Chapterid("14")
 *    VerseReference("23")
 * BookReference
 *   ...
 *
 * This can be represented in JSON in a simpler way:
 * [
 *  {
 *      'bookId' : '1Kor',
 *      'chapters': [
 *          {
 *              'chapterId': '13',
 *              'verses': [{ 'from':'1a', 'to':'3' },{ 'from':'5b', 'to':'14' },{ 'from':'6a'}]
 *          }, {
 *              'chapterId': '14', 'verses': [{'from':'23'}]
 *          }
 *      ]
 *  },
 *  ....
 * ]
 */
class CanonicalReference {
    public $bookRefs;

    public function getCode() {
        $code = "{$this->bookAbbrev}";
    }

    public static function fromCanonicalString($s) {
        $ref = new CanonicalReference();
        $bookAbbrevRegex = '(\d?\p{L}+)';
        $bookReferenceRegex  = "{$bookAbbrevRegex}";
        $canonicalReferenceRegex = "{$bookReferenceRegex}(; {$bookReferenceRegex})*";
        if (preg_match("/^{$canonicalReferenceRegex}/u", $s, $matcher)) {
            $ref->bookRefs = array();
            print_r($matcher);
            $ref->bookRefs[] = array('bookAbbrev'=>$matcher[1]);
        }

        return $ref;
    }
}

/**
 * Utility methods to process bible references.
 *
 * @author berti
 * 
 */
class ReferenceHandler {
    
 /**
 * Return a normalized standard version of a bible reference.
 * @param string $reference E.g. '1Kor 13, 1-13', '2Kor 2,3b.4-10;4,2-10a'
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
                                            ->where('chapter', $c)->orderBy('numv', 'desc')->first()->numv;

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
                                    ->where('chapter', $c)->orderBy('numv', 'desc')->first()->numv;
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
