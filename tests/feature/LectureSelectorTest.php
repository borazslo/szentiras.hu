<?php

namespace SzentirasHu\Test;

use App;
use Mockery;
use SzentirasHu\Http\Controllers\Home\LectureSelector;
use SzentirasHu\Service\LectureDownloader;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Test\Common\TestCase;

class LectureSelectorTest extends TestCase {

    private $downloader;
    private $textService;

    public function setUp() {
        parent::setUp();
        $this->downloader = Mockery::mock(LectureDownloader::class);
        $this->app->instance(LectureDownloader::class, $this->downloader);
        $this->textService = Mockery::mock(TextService::class);
        $this->app->instance(TextService::class, $this->textService);
    }

    public function testEmpty() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn("");
        $selector = App::make(LectureSelector::class);
        $lectures = $selector->getLectures();
        $this->assertEmpty($lectures);
    }

    public function testSimple() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');
        $this->textService->shouldReceive('getTeaser')->andReturn('teaser mock');
        $this->textService->shouldReceive('getTranslatedVerses')->andReturn([]);
        $selector = App::make(LectureSelector::class);
        $lectures = $selector->getLectures();
        $this->assertCount(2, $lectures);
    }

} 
