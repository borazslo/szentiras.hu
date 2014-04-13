<?php

namespace SzentirasHu\Lib;

use ErrorException;

class LectureDownloader {

    public $date;

    public function __construct($date) {
        $this->date = $date;
    }

    public function getReferenceString() {
        $fn2 = "http://katolikus.hu/igenaptar/{$this->date}.html";
        try {
            $text = file_get_contents($fn2);
            preg_match('/<!-- helyek:(.*)-->/', $text, $places);
            $referenceString = isset($places[1]) ? trim($places[1]) : '';
            return $referenceString;
        } catch (ErrorException $e) {
            return null;
        }
    }
}
