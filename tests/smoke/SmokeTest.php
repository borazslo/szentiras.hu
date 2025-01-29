<?php

namespace SzentirasHu\Test\Smoke;

use Mockery;
use SzentirasHu\Service\LectureDownloader;
use SzentirasHu\Service\Search\SearcherFactory;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Test\Common\TestCase;

class SmokeTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $downloader = Mockery::mock(LectureDownloader::class);
        $this->app->instance(LectureDownloader::class, $downloader);
        $downloader->shouldReceive('getReferenceString')->andReturn('Ter 4,5; Kiv 3,4');

        $textService = Mockery::mock(TextService::class);
        $this->app->instance(TextService::class, $textService);
        $textService->shouldReceive('getTeaser')->andReturn('teaser mock');
        $textService->shouldReceive('getTranslatedVerses')->andReturn([]);

        $searcherFactory = Mockery::mock(SearcherFactory::class);
        $this->app->instance(SearcherFactory::class, $searcherFactory);
        $searcherFactory->shouldReceive('createSearcherFor')->andReturn(new SearcherStub());

    }

    /**
     * Basic home page test.
     *
     * @return void
     */
    public function testBasicHomePage()
    {
        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');

        $this->get('/')->assertStatus(200);

    }

    public function testBasicTranslationPage()
    {
        $this->get('/TESTTRANS')->assertStatus(200);
    }

    public function testBasicApi()
    {
        $this->get('/api/idezet/Ter 2,3')->assertStatus(200);
    }

    public function testBasicApiTranslation()
    {
        $this->get('/api/forditasok/10100100200')->assertStatus(200);
    }

    public function testBasicSearch()
    {
        $this->get('/kereses/search?textToSearch=Ter&book=all&translation=0&grouping=chapter')->assertStatus(200);
    }

    public function testBookWithExplicitTranslation() {
        $this->get('/TESTTRANS/Ter')->assertStatus(200);
    }

    public function testChapterWithExplicitTranslation() {
        $this->get('/TESTTRANS/Ter2')->assertStatus(200);
    }

    public function testRefWithExplicitTranslation() {
        $this->get('/TESTTRANS/Ter2,3')->assertStatus(200);
    }

}