<?php

class SmokeTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $downloader = Mockery::mock('SzentirasHu\Lib\LectureDownloader');
        $this->app->instance('SzentirasHu\Lib\LectureDownloader', $downloader);
        $downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');

        $textService = Mockery::mock('SzentirasHu\Lib\Text\TextService');
        $this->app->instance('SzentirasHu\Lib\Text\TextService', $textService);
        $textService->shouldReceive('getTeaser')->andReturn('teaser mock');
        $textService->shouldReceive('getTranslatedVerses')->andReturn([]);

        $searcherFactory = Mockery::mock('SzentirasHu\Lib\Search\SearcherFactory');
        $this->app->instance('SzentirasHu\Lib\Search\SearcherFactory', $searcherFactory);
        $searcherFactory->shouldReceive('createSearcherFor')->andReturn(new SearcherStub());


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

    public function testBasicTranslationPage()
    {
        $this->client->request('GET', '/TESTTRANS');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testBasicApi()
    {
        $this->client->request('GET', '/api/idezet/Ter 2,3');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testBasicApiTranslation()
    {
        $this->client->request('GET', '/api/forditasok/10100100200');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testBasicSearch()
    {
        $this->client->request('GET', '/kereses/search?textToSearch=Ter&book=all&translation=0&grouping=chapter');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testBookWithExplicitTranslation() {
        $this->client->request('GET', '/TESTTRANS/Ter');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testChapterWithExplicitTranslation() {
        $this->client->request('GET', '/TESTTRANS/Ter2');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testRefWithExplicitTranslation() {
        $this->client->request('GET', '/TESTTRANS/Ter2,3');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

}