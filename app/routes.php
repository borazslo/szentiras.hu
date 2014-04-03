<?php

require('./views/Composers/viewComposers.php');

Route::get('/', "SzentirasHu\\Controllers\\Home\\HomeController@index");

Route::resource("search", "SearchController");

Route::get('/{TRANSLATION_ABBREV}', 'SearchController@show')
->where('TRANSLATION_ABBREV', '[A-Z]+');