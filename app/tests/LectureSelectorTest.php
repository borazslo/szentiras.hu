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

    public function testExtLink() {
        $this->downloader->shouldReceive('getReferenceString')->andReturn('1Moz 2,3; Kiv 3,4');
        $selector = new LectureSelector();
        $lectures = $selector->getLectures();
        $this->assertCount(2, $lectures);
        $this->assertEquals("/TESTTRANS/Ter2,3", $lectures[0]->extLinks[0]->url);
        $this->assertEquals("/TESTTRANS/Kiv3,4", $lectures[1]->extLinks[0]->url);
        $this->assertEquals("/TESTTRANS2/Ter2,3", $lectures[0]->extLinks[1]->url);
    }

} 