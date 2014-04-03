<?php

namespace SzentirasHu\Lib\Reference;

/**
 * Class CanonicalReference to represent a unique reference to some Bible verses.
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

}
