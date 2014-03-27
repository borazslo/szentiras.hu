<?php

class Lecture {
    public $link;
    public $extLinks = array();
    public $ref;
}

class ExtLink {
    
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
        $olvasmany_rov = isset($places[1]) ? trim($places[1]) : '';
	$olvasmanyok = explode(';',$olvasmany_rov);
        
        $resultLectures = array();
        
        foreach ($olvasmanyok as $olvasmany) {
            // convert Psalm numbering
            $lecture = new Lecture();
            $lecture->ref = $olvasmany;
            $resultLectures[] = $lecture;
        }
        return $resultLectures;
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