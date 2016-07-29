<?php

namespace SzentirasHu\Service\Imagine;

use Illuminate\Support\Facades\Facade;

class ImagineFacade extends Facade {
	protected static function getFacadeAccessor()
	{
		return 'imagine';
	}
}
