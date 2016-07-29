<?php
/**

 */

namespace SzentirasHu\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class RepositoriesProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('SzentirasHu\Data\Repository\BookRepository', 'SzentirasHu\Data\Repository\BookRepositoryEloquent');
        $this->app->bind('SzentirasHu\Data\Repository\TranslationRepository', 'SzentirasHu\Data\Repository\TranslationRepositoryEloquent');
        $this->app->bind('SzentirasHu\Data\Repository\VerseRepository', 'SzentirasHu\Data\Repository\VerseRepositoryEloquent');
        $this->app->bind('SzentirasHu\Data\Repository\SynonymRepository', 'SzentirasHu\Data\Repository\SynonymRepositoryEloquent');

    }
}