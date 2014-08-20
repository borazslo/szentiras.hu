<?php

namespace SzentirasHu\Lib;

use Cache;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Support\Facades\Log;

class LectureDownloader {

    const LECTURE_CACHE_KEY = 'szentiras.lecture';

    /**
     * @param Carbon $date
     * @return null|string
     */
    public function getReferenceString($date = null) {
        $downloadedDate = $date ? $date : Carbon::now();
        $dailyLecture = "http://katolikus.hu/igenaptar/{$downloadedDate->format('Ymd')}.html";
        try {
            if (!$date) {
                // today's lecture is cached
                $text = Cache::remember(self::LECTURE_CACHE_KEY, $downloadedDate->tomorrow()->addMinute(), function() use ($dailyLecture)
                {
                    return file_get_contents($dailyLecture);
                });
            } else {
                $text = file_get_contents($dailyLecture);
            }
            preg_match('/<!-- helyek:(.*)-->/', $text, $places);
            $referenceString = isset($places[1]) ? trim($places[1]) : '';
            return $referenceString;
        } catch (ErrorException $e) {
            \Log::error($e);
            return null;
        }
    }
}
