<?php

namespace SzentirasHu\Service\Reference;

class ReferenceLexer extends AbstractLexer {

    const T_CHAPTER_VERSE_SEPARATOR = 1;
    const T_VERSE_RANGE_SEPARATOR = 2;
    const T_RANGE_OPERATOR = 3;
    const T_TEXT = 4;
    const T_NUMERIC = 5;
    const T_BOOK_SEPARATOR = 6;
    const T_CHAPTER_RANGE_SEPARATOR = 7;

    public function __construct($input) {
        $this->setInput(trim($input));
    }

    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected function getCatchablePatterns() {
        return [
            '\d+',
            '\p{L}+',
            '[,:\-\.;\|–—]',
        ];
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns() {
        return array('\s+', '(.)');
    }

    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     * @return integer
     */
    protected function getType(&$value) {
        if (is_numeric($value)) {
            return self::T_NUMERIC;
        } else {
            switch ($value) {
                case ',' :
                case ':' :
                    return self::T_CHAPTER_VERSE_SEPARATOR;
                case '.' :
                    return self::T_VERSE_RANGE_SEPARATOR;
                case '-' :
                case '–':
                case '—':
                    return self::T_RANGE_OPERATOR;
                case ';' :
                    return self::T_BOOK_SEPARATOR;
                case '|' :
                    return self::T_CHAPTER_RANGE_SEPARATOR;
                default:
                    return self::T_TEXT;
            }
        }
    }
}