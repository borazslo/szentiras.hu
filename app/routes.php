<?php

require('views/Composers/viewComposers.php');

Route::get('/', "SzentirasHu\\Controllers\\Home\\HomeController@index");

Route::resource("search", "SearchController");

Route::get('/{TRANSLATION_ABBREV}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showTranslation')
->where('TRANSLATION_ABBREV', Config::get('settings.translationAbbrevRegex'));

Route::get('/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showReferenceText')
        ->where('REFERENCE', '[^/]+');

Route::get('/{TRANSLATION_ABBREV}/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showReferenceText')
        ->where(['TRANSLATION_ABBREV' => Config::get('settings.translationAbbrevRegex'),
                'REFERENCE'=>'[^/]+']);