<?php
/**

 */

namespace SzentirasHu\Controllers\Display;


use SzentirasHu\Lib\Reference\ChapterRange;

class DisplayHelper {

    /**
     * @param ChapterRange $chapterRange
     * @return array
     */
    public static function collectChapterIds($chapterRange)
    {
        $searchedChapters = [];
        $currentChapter = $chapterRange->chapterRef->chapterId;
        do {
            $searchedChapters[] = $currentChapter;
            $currentChapter++;
        } while ($chapterRange->untilChapterRef && $currentChapter <= $chapterRange->untilChapterRef->chapterId);
        return $searchedChapters;
    }


} 