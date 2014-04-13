<?php

require('views/Composers/viewComposers.php');

Route::get('/', "SzentirasHu\Controllers\Home\HomeController@index");

Route::get('/forditasok', 'SzentirasHu\Controllers\Display\TextDisplayController@showTranslationList');

Route::resource("search", "SearchController");

Route::get('/hang', 'SzentirasHu\Controllers\Display\AudioBookController@index');

Route::get('/hang/{id}', 'SzentirasHu\Controllers\Display\AudioBookController@show')
->where('id', '.+');

Route::get('/{TRANSLATION_ABBREV}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showTranslation')
->where('TRANSLATION_ABBREV', Config::get('settings.translationAbbrevRegex'));

Route::get('/{TRANSLATION_ABBREV}/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showTranslatedReferenceText')
        ->where(['TRANSLATION_ABBREV' => Config::get('settings.translationAbbrevRegex'),
                'REFERENCE'=>'[^/]+']);

Route::get('/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showReferenceText')
    ->where('REFERENCE', '[^/]+');

