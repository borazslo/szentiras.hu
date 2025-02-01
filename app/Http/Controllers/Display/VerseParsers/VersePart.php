<?php

namespace SzentirasHu\Http\Controllers\Display\VerseParsers;

enum VersePartType: string {
    case POEM_LINE = 'poem_line';
    case SIMPLE_TEXT = 'simple_text';
    case HEADING = 'heading';
}

class VersePart
{
    public string $content;
    public VersePartType $type;
    public int $headingLevel;
    public int $order; // order of the parts, useful to find out if something is at the beginning (or end) of the verse
    public VerseData $verse;

    public function __construct(VerseData $verse, string $content, VersePartType $type, int $order, int $headingLevel = 0) {
        $this->content = $content;
        $this->type = $type;
        $this->order = $order;
        $this->headingLevel = $headingLevel;
        $this->verse = $verse;
    }

    public function getContent() {
        return $this->content;
    }

    public function isPoem() {
        return $this->type == VersePartType::POEM_LINE;
    }

    public function isFirst() {
        return $this->order == 0;
    }

    public function isLast() {
        return $this->order == $this->verse->getCount()-1;
    }
}