<?php

return [
    'defaultTranslationId' => env("DEFAULT_TRANSLATION_ID", 3),
    'defaultTranslationAbbrev' => env("DEFAULT_TRANSLATION_ABBREV", "KNB"),
    'translationAbbrevRegex' => env("TRANSLATION_ABBREV_REGEX", "KNB|SZIT|UF|KG|BD|RUF|knb|szit|uf|kg|bd|ruf"),
    'enabledTranslations' => preg_split("/, ?/", env("ENABLED_TRANSLATIONS", "1,2")),
    'audioDirectory' => env("AUDIO_DIRECTORY", 'hang'),
    'sourceDirectory' => '/tmp',
    'facebookAppId' => '679257202109581',
    'searchLimit' => 1000,
    'logLevel' => env("LOG_LEVEL", 'debug'),
    'imageMagickCommand' => [ 'gm', 'convert' ],
    'sphinxConfig' => env('SPHINX_CONFIG', '/etc/sphinxsearch/sphinx.conf'),
    'sphinxPort' => env('SPHINX_PORT', 9312),
    'sphinxIndexes' => [
        'name' => ['verse', 'verse_root'],
        'mapping' => false
    ],
    'googleAppName' => 'szentiras-hu',
    'googleApiKey' => env('GOOGLE_API_KEY'),
    'googleCalendarId' => 'katolikus.hu@gmail.com'
];