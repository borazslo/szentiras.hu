<?php

return [
	'fetch' => PDO::FETCH_CLASS,
	'default' => 'bible',
	'connections' => array(
		'bible' => array(
			'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => 'bible',
			'username'  => getenv("MYSQL_SZENTIRAS_USER"),
			'password'  => getenv("MYSQL_SZENTIRAS_PASSWORD"),
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'kar_',
		),
	),

	'migrations' => 'migrations',
];
