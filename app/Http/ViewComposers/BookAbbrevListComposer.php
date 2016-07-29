<?php
/**

 */

namespace SzentirasHu\Http\ViewComposers;


use SzentirasHu\Data\Repository\TranslationRepository;

class BookAbbrevListComposer
{


    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
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