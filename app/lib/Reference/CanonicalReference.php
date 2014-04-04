<?php

namespace SzentirasHu\Lib\Reference;
use SzentirasHu\Models\Entities\Translation;

/**
 * Class CanonicalReference to represent a unique reference to some Bible verses.
 * This reference is agnostic of translations, uses the primary
 *
 */
class CanonicalReference {

    /**
     * @var BookRef[]
     */
    public $bookRefs;

    public static function fromString($s) {
        $ref = new CanonicalReference();
        $parser = new ReferenceParser($s);
        $bookRefs = $parser->bookRefs();
        $ref->bookRefs = $bookRefs;
        return $ref;
    }

    public function toString() {
        $s = '';
        $lastBook = end($this->bookRefs);
        foreach ($this->bookRefs as $bookRef) {
            $s.=$bookRef->toString();
            if ($lastBook !== $bookRef) {
                $s.="; ";
            }
        }
        return $s;
    }

    /**
     *
     * Takes a bookref and get an other bookref according
     * to the given translation.
     */
    public static function toTranslated(BookRef $bookRef, $translationId) {

       return null;
    }

}
