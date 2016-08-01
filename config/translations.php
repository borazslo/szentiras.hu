<?php

return [
    'KNB' => [
        'verseTypes' =>
        [
            'text' => [60],
            'heading' => [0=>5, 1=>10, 2=>20, 3=>30],
            'footnote' => [120]
        ],
        'textSource' => env('TEXT_SOURCE_KNB')
    ],

    'KG' => [
        'verseTypes' =>
        [
            'text' => [6],
            'heading' => [1=>1, 2=>2, 3=>3],
            'xref' => [13]
        ],
        'textSource' => env('TEXT_SOURCE_KG')
    ],
    'SZIT' => [
        'verseTypes' =>
        [
            'text' => [901],
            'heading' => [0=>401, 1=>501, 2=>601, 3=>701, 4=>704],
            'footnote' => [2001]
        ],
        'textSource' => env('TEXT_SOURCE_SZIT')
    ],
    'UF' => [
        'verseTypes' =>
        [
            'text' => [901],
            'heading' => [3=>703]
        ],
        'textSource' => env('TEXT_SOURCE_UF')
    ],
    'BD' => [
        'verseTypes' =>
        [
            'text' => [901],
            'heading' => [2=>701, 3=>704]
        ],
        'textSource' => env('TEXT_SOURCE_BD')
    ],
    'RUF' => [
        'verseTypes' =>
        [
            'text' => [901],
            'heading' => [3=>701],
            'footnote' => [2001],
            'xref' => [2021]
        ],
        'textSource' => env('TEXT_SOURCE_RUF')
    ]
];