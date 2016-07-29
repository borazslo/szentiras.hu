<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'Home\HomeController@index');

Route::controller("/kereses", 'Search\SearchController');

Route::post('/searchbible.php', 'SzentirasHu\Http\Controllers\Search\SearchController@postLegacy');

/** API */
Route::controller("/api", 'Api\ApiController');

Route::controller('/info', 'Home\InfoController');

Route::controller('/pdf', 'Display\PdfController');

Route::get('/API', function () {
    if (Input::get('feladat') === 'idezet') {
        return Redirect::action('SzentirasHu\Http\Controllers\Api\ApiController@getIdezet', [Input::get('hivatkozas'), Input::get('forditas')], 301);
    } else if (Input::get('feladat') === '') {
        return Redirect::action('SzentirasHu\Http\Controllers\Api\ApiController@getForditasok', [Input::get('hivatkozas')], 301);
    }
    return Redirect::to('api');
});

/** AUDIO */

Route::get('/hang', 'Display\AudioBookController@index');

Route::get('/hang/{id}', 'Display\AudioBookController@show')
    ->where('id', '.+');

/** TEXT DISPLAY */

/** QR code */
Route::get('/qr/dialog/{url}', 'Display\\QrCodeController@dialog')->where('url', '.+');
Route::get('/qr/img/{url}', 'Display\\QrCodeController@index')->where('url', '.+');

Route::get('/forditasok', 'Display\\TextDisplayController@showTranslationList');

Route::get('/{TRANSLATION_ABBREV}', 'Display\\TextDisplayController@showTranslation')
    ->where('TRANSLATION_ABBREV', Config::get('settings.translationAbbrevRegex'));

Route::get('/{TRANSLATION_ABBREV}/{REFERENCE}', 'Display\\TextDisplayController@showTranslatedReferenceText')
    ->where(['TRANSLATION_ABBREV' => Config::get('settings.translationAbbrevRegex'),
        'REFERENCE' => '[^/]+']);

Route::get('/{REFERENCE}', 'Display\\TextDisplayController@showReferenceText')
    ->where('REFERENCE', '[^/]+');

