<?php

namespace SzentirasHu\Service;

use Cache;
use Carbon\Carbon;
use ErrorException;
use Google_Auth_AssertionCredentials;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Log;

class LectureDownloader {

    const LECTURE_CACHE_KEY = 'szentiras.lecture';

    /**
     * @param Carbon $date
     * @return null|string
     */
    public function getReferenceString($date = null) {
        $referenceString = null;
        // $referenceString = $this->getReferenceStringGoogleCal($date);
        if (!$referenceString) {
            $referenceString = $this->getReferenceStringKatolikusHu($date);
        }
        return $referenceString;
    }

    /**
     * @param Carbon $date
     */
    private function getReferenceStringGoogleCal($date) {
        $client = new Google_Client();
        $client->setApplicationName(\Config::get('settings.googleAppName'));
        $client->setDeveloperKey(\Config::get('settings.googleApiKey'));
        /** @var Google_Service_Calendar $service */
        $service = new Google_Service_Calendar($client);
        $calendarId = \Config::get('settings.googleCalendarId');
        $params = array(
            'singleEvents' => true,
            'orderBy' => 'startTime',
            'timeMin' => date(DATE_ATOM),
            'maxResults' => 7
        );
        $events = $service->events->listEvents($calendarId, $params);
        /** @var \Google_Service_Calendar_Event $event */
        foreach ($events->getItems() as $event) {
            \Log::debug($event->getSummary());
        }

    }

    private function getReferenceStringKatolikusHu($date) {
        $downloadedDate = $date ? $date : Carbon::now();
        $dailyLecture = "http://igenaptar.katolikus.hu/nap?holnap={$downloadedDate->format('Y-m-d')}";
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
