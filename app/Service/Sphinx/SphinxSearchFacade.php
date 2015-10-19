<?php

namespace SzentirasHu\Service\Sphinx;

use Illuminate\Support\Facades\Facade;

class SphinxSearchFacade extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'sphinxsearch';
	}
}
