<?php

namespace SzentirasHu\Test\Smoke;

use Mockery;
use SzentirasHu\Service\LectureDownloader;
use SzentirasHu\Service\Search\SearcherFactory;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Test\Common\TestCase;

class SmokeTest extends TestCase
{
    public function setUp()
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

        $this->visit('/')->assertResponseOk();

    }

    public function testBasicTranslationPage()
    {
        $this->visit('/TESTTRANS')->assertResponseOk();
    }

    public function testBasicApi()
    {
        $this->visit('/api/idezet/Ter 2,3')->assertResponseOk();
    }

    public function testBasicApiTranslation()
    {
        $this->visit('/api/forditasok/10100100200')->assertResponseOk();
    }

    public function testBasicSearch()
    {
        $this->visit('/kereses/search?textToSearch=Ter&book=all&translation=0&grouping=chapter')->assertResponseOk();
    }

    public function testBookWithExplicitTranslation() {
        $this->visit('/TESTTRANS/Ter')->assertResponseOk();
    }

    public function testChapterWithExplicitTranslation() {
        $this->visit('/TESTTRANS/Ter2')->assertResponseOk();
    }

    public function testRefWithExplicitTranslation() {
        $this->visit('/TESTTRANS/Ter2,3')->assertResponseOk();
    }

}