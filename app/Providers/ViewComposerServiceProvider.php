<?php
/**
 */

namespace SzentirasHu\Providers;


use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{

    public function boot() {
        view()->composer('menu', 'SzentirasHu\Http\ViewComposers\MenuComposer');
        view()->composer('bookAbbrevList', '\SzentirasHu\Http\ViewComposers\BookAbbrevListComposer');
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}