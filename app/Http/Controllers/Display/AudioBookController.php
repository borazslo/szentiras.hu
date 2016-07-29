<?php

namespace SzentirasHu\Http\Controllers\Display;

use App;
use Config;
use Exception;
use Log;
use Response;
use View;

class AudioBookController extends \BaseController
{

    public function index()
    {
        return View::make('audioBook');
    }

    public function show($id)
    {
        Log::debug("show", [$id]);
        $dir = Config::get('settings.audioDirectory');
        if (!strstr($id, '..')){
            try {
                $path = "{$dir}/{$id}";
                Log::debug('downloading', [$id, $path]);
                return Response::download($path);
            } catch (Exception $e) {
                Log::debug('exception', [$e]);
                App::abort(404, '404 :(');
            }
        }
    }

}