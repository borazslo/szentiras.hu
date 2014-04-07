<?php

namespace SzentirasHu\Controllers\Home;

use SzentirasHu\Models\Entities\Article;
use SzentirasHu\Models\Entities\News;
use SzentirasHu\Models\Entities\Translation;

/**
 *
 * Controller for the home page.
 * Note that many parts on the home view are coming from view composers.
 */
class HomeController extends \BaseController
{

    public function index()
    {
        return \View::make("home", [
            'news' => Article::where('frontpage', true)->orderBy('publish_date', 'desc')->get(),
            'pageTitle' => 'Fordítások | Szentírás',
            'title' => 'Katolikus bibliafordítások',
            'cathBibles' => Translation::getByDenom('katolikus'),
            'otherBibles' => Translation::getByDenom('protestáns'),
            'lectures' => (new LectureSelector())->getLectures()
        ]);
    }

}