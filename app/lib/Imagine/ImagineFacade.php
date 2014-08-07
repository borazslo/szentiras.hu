<?php

namespace SzentirasHu\Lib\Imagine;

use Illuminate\Support\Facades\Facade;

class ImagineFacade extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'imagine';
	}
}
