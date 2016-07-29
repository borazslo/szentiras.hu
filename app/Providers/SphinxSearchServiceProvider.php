<?php

namespace SzentirasHu\Providers;

use Illuminate\Support\ServiceProvider;
use SzentirasHu\Service\Sphinx\SphinxSearch;

class SphinxSearchServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['sphinxsearch'] = $this->app->share(function($app)
		{
			return new SphinxSearch;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('sphinxsearch');
	}

}
