<?php

class Lecture {
    public $link;
    public $extLinks = array();
    public $ref;
    public $translationId;
    public $bookAbbrev;
}

class ExtLink {
    public $url;
    public $title;
    public $label;
}

class LectureDownloader {

    private $date;
    
    public function __construct($date=false) {
        $this->date = $date ? $date : date("Ymd");
    }    
  
    public function getLectures() {
        $fn2 = "http://katolikus.hu/igenaptar/{$this->date}.html";
	$text = file_get_contents($fn2);
        preg_match('/<!-- helyek:(.*)-->/', $text, $places);
        $referenceString = isset($places[1]) ? trim($places[1]) : '';
	$references = explode(';', $referenceString);
        
        $resultLectures = array();
        
        foreach ($references as $reference) {
            // extract and convert Psalm numbering
            if(preg_match('/Zs ([0-9]{1,3})/', $reference, $matches)) {
                $vulgataNum = (int)$matches[1];
                $reference = 'Zsolt '.$this->getHebrewPsalmNum($vulgataNum);
            }
            $normalizedReference = (new ReferenceHandler())->normalizeReference($reference, 4);
            $lecture = new Lecture();
            $lecture->ref = $normalizedReference['code'];
            $lecture->link = $normalizedReference['url'];
            $lecture->translationId = $normalizedReference['reftrans'];
            $lecture->bookAbbrev = $normalizedReference['book'];
            
            $extLinks = array();
            
            $verse = Verse::where('trans', $lecture->translationId)
                    ->whereHas('book', function($q) use ($lecture) {
                        $q->where('abbrev', $lecture->bookAbbrev);
                    })->select('gepi')->first();
            if ($verse) {            
                $availableTranslatedVerses = Verse::whereIn('tip', array(60, 6, 901))
                        ->where('gepi', $verse->gepi)->get();
                foreach ($availableTranslatedVerses as $verse) {
                    $translation = $verse->translation;
                    $extLink = new ExtLink();          
                    $extLink->label = $translation->abbrev;
                    $extLink->url = "/{$translation->abbrev}/{$normalizedReference['url']}";
                    $extLinks[] = $extLink;
                }
            }
            
            
            $lecture->extLinks = $extLinks;
            $resultLectures[] = $lecture;
        }
        return $resultLectures;
    }
    
    private function getHebrewPsalmNum($vulgataNum) {
        // see http://en.wikipedia.org/wiki/Psalms#Numbering
        if ($vulgataNum >= 10 && $vulgataNum <= 113
                || $vulgataNum >= 116 && $vulgataNum <= 145) {
            $hebrewNum = $vulgataNum + 1;
        } else if ($vulgataNum == 114 || $vulgataNum == 115) {
            $hebrewNum = 116;
        } else if ($vulgataNum == 146 || $vulgataNum == 147) {
            $hebrewNum = 146;
        } else {
            $hebrewNum = $vulgataNum;
        }
        return $hebrewNum;
    }
}

/**
 *
 * Controller for the home page.
 * Note that many parts on the home view are coming from view composers.
 */
class HomeController extends BaseController {

    public function index() {
        return View::make("home", array(
            'news' => News::where('frontpage','1')->orderBy('date','desc')->get(),
            'pageTitle' => 'Fordítások | Szentírás',
            'title' => 'Katolikus bibliafordítások',
            'cathBibles' => Translation::getByDenom('katolikus'),
            'otherBibles' => Translation::getByDenom('protestáns'),
            'olvasmanyok' => (new LectureDownloader())->getLectures()
        ));
    }

}