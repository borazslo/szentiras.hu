<?php

namespace SzentirasHu\Controllers\Home;

use BaseController;
use SzentirasHu\Models\Entities\Article;
use SzentirasHu\Models\Repositories\TranslationRepository;

/**
 *
 * Controller for the home page.
 * Note that many parts on the home view are coming from view composers.
 */
class InfoController extends BaseController
{

    public function getIndex()
    {
        return \View::make("info.info", [
            'news' => Article::where('frontpage', true)->orderBy('publish_date', 'desc')->get(),
        ]);
    }

}