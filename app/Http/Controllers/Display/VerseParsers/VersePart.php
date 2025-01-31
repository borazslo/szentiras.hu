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

    public function __construct(string $content, VersePartType $type, int $order, int $headingLevel = 0) {
        $this->content = $content;
        $this->type = $type;
        $this->order = $order;
        $this->headingLevel = $headingLevel;
    }

    public function getContent() {
        return $this->content;
    }

    public function isPoem() {
        return $this->type == VersePartType::POEM_LINE;
    }
}