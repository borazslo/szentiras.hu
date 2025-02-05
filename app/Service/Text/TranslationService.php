<?php

namespace SzentirasHu\Service\Text;

use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Repository\TranslationRepository;

class TranslationService {

    public function __construct(protected TranslationRepository $translationRepository) {
    }

    public function getDefaultTranslation() {
        $defaultTranslationId = \Config::get('settings.defaultTranslationId');
        return $this->translationRepository->getById($defaultTranslationId);
    }

}