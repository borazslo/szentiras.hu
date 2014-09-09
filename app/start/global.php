<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useDailyFiles(storage_path().'/logs/laravel.log', 0, Config::get('settings.logLevel'));

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code)
{
	Log::error("Exception on request: " . Request::url() . " referer: " . Request::header('Referer') . ". " . $exception);
    if (!Config::get('app.debug')) {
        return Response::make('Hiba történt. <a href="/">Vissza a címlapra</a>', 500);
    }
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Karbantartás - pár perc múlva visszatérünk.", 503);
});


App::missing(function($exception) {
    Log::warning("404 for request: ". Request::url());
    return Response::make('A kért oldal nem található. <a href="/">Vissza a címlapra</a>', 404);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

Event::listen("illuminate.query", function($query, $bindings, $time, $name){
    Log::debug($query.' bindings:'.implode(',',$bindings));
});

App::instance("verseParsers", [
    1 => App::make('SzentirasHu\Lib\Text\VerseParsers\DefaultVerseParser'),
    2 => App::make('SzentirasHu\Lib\Text\VerseParsers\DefaultVerseParser'),
    3 => App::make('SzentirasHu\Lib\Text\VerseParsers\KNBVerseParser'),
    4 => App::make('SzentirasHu\Lib\Text\VerseParsers\KGVerseParser'),

]);