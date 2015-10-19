<?php
/**

 */

namespace SzentirasHu\Service\Reference;


use Exception;

class ParsingException extends Exception {

    public function __construct($position, $code = 0, Exception $previous = null) {
        parent::__construct("Parse error at position {$position}", $code, $previous);
    }

} 