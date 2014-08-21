<?php

require('views/Composers/viewComposers.php');

Route::get('/', 'SzentirasHu\Controllers\Home\HomeController@index');

Route::controller("/kereses", 'SzentirasHu\Controllers\Search\SearchController');

Route::post('/searchbible.php', 'SzentirasHu\Controllers\Search\SearchController@postLegacy');

/** API */
Route::controller("/api", 'SzentirasHu\Controllers\Api\ApiController');

Route::controller('/info', 'SzentirasHu\Controllers\Home\InfoController');

Route::controller('/pdf', 'SzentirasHu\Controllers\Display\PdfController');

Route::get('/API', function () {
    if (Input::get('feladat') === 'idezet') {
        return Redirect::action('SzentirasHu\Controllers\Api\ApiController@getIdezet', [Input::get('hivatkozas'), Input::get('forditas')], 301);
    } else if (Input::get('feladat') === '') {
        return Redirect::action('SzentirasHu\Controllers\Api\ApiController@getForditasok', [Input::get('hivatkozas')], 301);
    }
    return Redirect::to('api');
});

/** AUDIO */

Route::get('/hang', 'SzentirasHu\Controllers\Display\AudioBookController@index');

Route::get('/hang/{id}', 'SzentirasHu\Controllers\Display\AudioBookController@show')
    ->where('id', '.+');

/** TEXT DISPLAY */

/** QR code */
Route::get('/qr/dialog/{url}', 'SzentirasHu\Controllers\Display\QrCodeController@dialog')->where('url', '.+');
Route::get('/qr/img/{url}', 'SzentirasHu\Controllers\Display\QrCodeController@index')->where('url', '.+');

Route::get('/forditasok', 'SzentirasHu\Controllers\Display\TextDisplayController@showTranslationList');

Route::get('/{TRANSLATION_ABBREV}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showTranslation')
    ->where('TRANSLATION_ABBREV', Config::get('settings.translationAbbrevRegex'));

Route::get('/{TRANSLATION_ABBREV}/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showTranslatedReferenceText')
    ->where(['TRANSLATION_ABBREV' => Config::get('settings.translationAbbrevRegex'),
        'REFERENCE' => '[^/]+']);

Route::get('/{REFERENCE}', 'SzentirasHu\\Controllers\\Display\\TextDisplayController@showReferenceText')
    ->where('REFERENCE', '[^/]+');

