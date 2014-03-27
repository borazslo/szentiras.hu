<?php

class HomeController extends BaseController {

    public function index()
	{
            $translations = Translation::where('denom','katolikus')->orderBy('name')->get();
            return View::make("home", array('translations'=>$translations));
	}

}