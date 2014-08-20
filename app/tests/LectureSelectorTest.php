<?php

use SzentirasHu\Controllers\Home\LectureSelector;

class LectureSelectorTest extends TestCase {

    private $downloader;
    private $textService;

    public function setUp() {
        parent::setUp();
        $this->downloader = Mockery::mock('SzentirasHu\Lib\LectureDownloader');
        $this->app->instance('SzentirasHu\Lib\LectureDownloader', $this->downloader);
        $this->textService = Mockery::mock('SzentirasHu\Lib\Text\TextService');
        $this->app->instance('SzentirasHu\Lib\Text\TextService', $this->textService);
    }

    public function testEmpty() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn("");
        $selector = App::make('SzentirasHu\Controllers\Home\LectureSelector');
        $lectures = $selector->getLectures();
        $this->assertEmpty($lectures);
    }

    public function testSimple() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');
        $this->textService->shouldReceive('getTeaser')->andReturn('teaser mock');
        $this->textService->shouldReceive('getTranslatedVerses')->andReturn([]);
        $selector = App::make('SzentirasHu\Controllers\Home\LectureSelector');
        $lectures = $selector->getLectures();
        $this->assertCount(2, $lectures);
    }

} 
