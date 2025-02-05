<?php

namespace SzentirasHu\Service\Reference;

class VerseRef
{
    public int $verseId;
    public string $versePart = '';

    function __construct($verseId)
    {
        $this->verseId = $verseId;
    }

    public function toString()
    {
        return $this->verseId . $this->versePart;
    }
}