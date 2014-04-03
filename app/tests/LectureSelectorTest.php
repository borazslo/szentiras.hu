<?php

use SzentirasHu\Controllers\Home\LectureSelector;

class LectureSelectorTest extends TestCase {

    private $downloader;

    public function setUp() {
        parent::setUp();
        $this->downloader = Mockery::mock('LectureDownloader');
        $this->app->instance('LectureDownloader', $this->downloader);
    }

    public function testEmpty() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn("");
        $selector = new LectureSelector();
        $lectures = $selector->getLectures();
        $this->assertEmpty($lectures);
    }

    public function testSimple() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn('1Ter 4,5; 1Jn 2-3');
        $selector = new LectureSelector();
        $lectures = $selector->getLectures();
        $this->assertCount(2, $lectures);
    }

} 