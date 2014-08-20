<?php

use SzentirasHu\Controllers\Home\LectureSelector;

class LectureSelectorTest extends TestCase {

    private $downloader;

    public function setUp() {
        parent::setUp();
        $this->downloader = Mockery::mock('SzentirasHu\Lib\LectureDownloader');
        $this->app->instance('SzentirasHu\Lib\LectureDownloader', $this->downloader);
    }

    public function testEmpty() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn("");
        $selector = App::make('SzentirasHu\Controllers\Home\LectureSelector');
        $lectures = $selector->getLectures();
        $this->assertEmpty($lectures);
    }

    public function testSimple() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');
        $selector = App::make('SzentirasHu\Controllers\Home\LectureSelector');
        $lectures = $selector->getLectures();
        $this->assertCount(2, $lectures);
    }

} 
