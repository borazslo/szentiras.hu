<?php

namespace SzentirasHu\Lib;

use Cache;
use Carbon\Carbon;
use ErrorException;

class LectureDownloader {

    const LECTURE_CACHE_KEY = 'szentiras.lecture';

    public function getReferenceString($date=false) {
        $date = $date ? $date : date("Ymd");
        $fn2 = "http://katolikus.hu/igenaptar/{$date}.html";
        try {
            $text = Cache::get(self::LECTURE_CACHE_KEY);
            if (!$text) {
                $text = file_get_contents($fn2);
                $tomorrow = Carbon::tomorrow();
                $tomorrow->setTime(0, 0, 0);
                Cache::put(self::LECTURE_CACHE_KEY, $text, $tomorrow);
            }

            preg_match('/<!-- helyek:(.*)-->/', $text, $places);
            $referenceString = isset($places[1]) ? trim($places[1]) : '';
            return $referenceString;
        } catch (ErrorException $e) {
            return null;
        }
    }
}
