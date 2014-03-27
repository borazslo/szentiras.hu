<?php

require('views/composers/viewComposers.php');

Route::get('/', "HomeController@index");

Route::resource("search", "SearchController");