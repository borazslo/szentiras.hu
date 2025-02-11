<?php

use Illuminate\Support\Facades\Route;
use SzentirasHu\Http\Controllers\Ai\AiController;

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

Route::get("/kereses", 'Search\SearchController@getIndex');
Route::get("/kereses/search", 'Search\SearchController@anySearch');
Route::post("/kereses/search", 'Search\SearchController@anySearch');
Route::get("/kereses/suggest", 'Search\SearchController@anySuggest');
Route::post("/kereses/suggest", 'Search\SearchController@anySuggest');
Route::post("/kereses/legacy", 'Search\SearchController@postLegacy');

Route::get("/ai-search", 'Search\SemanticSearchController@getIndex');
Route::get("/ai-search/search", 'Search\SemanticSearchController@anySearch');

Route::get("/ai-tool/{translationAbbrev}/{refString}", [AiController::class, 'getAiToolPopover']);

Route::post('/searchbible.php', 'SzentirasHu\Http\Controllers\Search\SearchController@postLegacy');

/** API */
Route::get("/api", 'Api\ApiController@getIndex');

Route::get('/info', 'Home\InfoController@getIndex');

Route::get('/pdf/dialog/{translationAbbrev}/{refString}', 'Display\PdfController@getDialog');
Route::get('/pdf/preview/{translationId}/{refString}', 'Display\PdfController@getPreview');
Route::get('/pdf/ref/{translationId}/{refString}', 'Display\PdfController@getRef');

/** AUDIO */

Route::get('/hang', 'Display\AudioBookController@index');

Route::get('/hang/{id}', 'Display\AudioBookController@show')
    ->where('id', '.+');

/** TEXT DISPLAY */

/** QR code */
Route::get('/qr/dialog/{url}', 'Display\\QrCodeController@dialog')->where('url', '.+');
Route::get('/qr/img/{url}', 'Display\\QrCodeController@index')->where('url', '.+');

Route::get('/forditasok', 'Display\\TextDisplayController@showTranslationList');

Route::get('/tervek/{plan_id}/{day_number}', 'Display\\TextDisplayController@showReadingPlanDay')
    ->where(['plan_id' => '.+', 'day_number' => '.+']);

Route::get('/tervek/{id}', 'Display\\TextDisplayController@showReadingPlan')
    ->where('id', '.+');

Route::get('/tervek', 'Display\\TextDisplayController@showReadingPlanList');

Route::get('/{TRANSLATION_ABBREV}', 'Display\\TextDisplayController@showTranslation')
    ->where('TRANSLATION_ABBREV', Config::get('settings.translationAbbrevRegex'));

Route::get('/{TRANSLATION_ABBREV}/{REFERENCE}', 'Display\\TextDisplayController@showTranslatedReferenceText')
    ->where(['TRANSLATION_ABBREV' => Config::get('settings.translationAbbrevRegex'),
        'REFERENCE' => '[^/]+']);

Route::get('/{REFERENCE}', 'Display\\TextDisplayController@showReferenceText')
     ->where('REFERENCE', '[^/]+');

