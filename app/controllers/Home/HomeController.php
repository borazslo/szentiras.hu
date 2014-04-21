<?php

namespace SzentirasHu\Controllers\Home;

use BaseController;
use SzentirasHu\Models\Entities\Article;
use SzentirasHu\Models\Entities\News;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\TranslationRepository;

/**
 *
 * Controller for the home page.
 * Note that many parts on the home view are coming from view composers.
 */
class HomeController extends BaseController
{

    /**
     * @var LectureSelector
     */
    private $lectureSelector;
    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(TranslationRepository $translationRepository, LectureSelector $lectureSelector)
    {
        $this->lectureSelector = $lectureSelector;
        $this->translationRepository = $translationRepository;
    }

    public function index()
    {
        return \View::make("home", [
            'news' => Article::where('frontpage', true)->orderBy('publish_date', 'desc')->get(),
            'pageTitle' => 'Fordítások | Szentírás',
            'title' => 'Katolikus bibliafordítások',
            'cathBibles' => $this->translationRepository->getByDenom('katolikus'),
            'otherBibles' => $this->translationRepository->getByDenom('protestáns'),
            'lectures' => $this->lectureSelector->getLectures()
        ]);
    }

}