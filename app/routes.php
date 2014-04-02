<?php

require('views/composers/viewComposers.php');

Route::get('/', "HomeController@index");

Route::resource("search", "SearchController");

Route::get('/{TRANSLATION_ABBREV}', 'SearchController@show')
->where('TRANSLATION_ABBREV', '[A-Z]+');