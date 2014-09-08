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
        try {
            $lectures = $this->lectureSelector->getLectures();
        } catch (\Exception $e) {
            $lectures = [];
        }

        return \View::make("home", [
            'pageTitle' => 'Szentírás - A Biblia teljes szövege, katolikus és protestáns fordításokban',
            'cathBibles' => $this->translationRepository->getByDenom('katolikus'),
            'otherBibles' => $this->translationRepository->getByDenom('protestáns'),
            'lectures' => $lectures
        ]);
    }

}