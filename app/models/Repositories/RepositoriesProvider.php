<?php
/**

 */

namespace SzentirasHu\models\Repositories;


use Illuminate\Support\ServiceProvider;

class RepositoriesProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('SzentirasHu\Models\Repositories\BookRepository', 'SzentirasHu\Models\Repositories\BookRepositoryEloquent');
        $this->app->bind('SzentirasHu\Models\Repositories\TranslationRepository', 'SzentirasHu\Models\Repositories\TranslationRepositoryEloquent');
        $this->app->bind('SzentirasHu\Models\Repositories\VerseRepository', 'SzentirasHu\Models\Repositories\VerseRepositoryEloquent');
        $this->app->bind('SzentirasHu\Models\Repositories\SynonymRepository', 'SzentirasHu\Models\Repositories\SynonymRepositoryEloquent');

    }
}