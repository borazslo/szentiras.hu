<?php

class SmokeTest extends TestCase {

    public function setUp() {
        parent::setUp();
        $downloader = Mockery::mock('SzentirasHu\Lib\LectureDownloader');
        $this->app->instance('SzentirasHu\Lib\LectureDownloader', $downloader);
        $downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');
    }

    /**
	 * Basic home page test.
	 *
	 * @return void
	 */
	public function testBasicHomePage()
	{
		$this->client->request('GET', '/');
		$this->assertTrue($this->client->getResponse()->isOk());

	}

    public function testBasicTranslationPage() {
        $this->client->request('GET', '/TESTTRANS');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testBasicApi() {
        $this->client->request('GET', '/api/idezet/Ter 2,3');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

}