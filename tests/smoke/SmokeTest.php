<?php

namespace SzentirasHu\Test\Smoke;

use Mockery;
use SzentirasHu\Service\LectureDownloader;
use SzentirasHu\Service\Search\SearcherFactory;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Test\Common\TestCase;

/* To run the app in this environment, you can use:
TRANSLATION_ABBREV_REGEX='TESTTRANS\d*' php artisan route:cache && DB_PREFIX=testing_ DEFAULT_TRANSLATION_ID=1 DEFAULT_TRANSLATION_ABBREV=TESTTRANS php artisan serve --port 1024 --env=testing
*/
class SmokeTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        /* Clean up caches, to not be affected by runtime */

        \Artisan::call('route:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('cache:clear');

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