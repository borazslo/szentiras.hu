<?php
/**

 */

namespace SzentirasHu\views\Composers;


use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\TranslationRepository;

class BookAbbrevListComposer
{


    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    public function compose($view)
    {
        $translation = array_key_exists('translation', $view->getData()) ?
            $view['translation'] :
            $this->translationRepository->getById(\Config::get('settings.defaultTranslationId'));
        $books = $this->translationRepository->getBooks($translation);
        $view
            ->with(['books' => $books, 'translation' => $translation]);
    }
}