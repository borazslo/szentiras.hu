<?php

return [
	'host'    => '127.0.0.1',
	'port'    => 9312,
	'indexes' => [
        'verse' => [ 'table' => 'tdverse', 'column' => 'id', 'modelname' => 'SzentirasHu\Models\Entities\Verse'],
        'verse_root' => false
    ]
];
