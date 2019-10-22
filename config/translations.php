<?php

/*
301	Korpuszcím
320	Belső korpusz címe
330	Könyvcím
340	Belső korpusz bevezetője
501	Címsor1
601	Címsor2
650	Címsor3
660	Zsoltárcím
665	Héber betűnév
670	Címsor4
680	Címsor5
701	Címsor6
850	Kihúzott sor
890	Kimaradó versszám (nullás)
891	Különlegesen írt versszám
892	Magyarázat a folyamatos szövegben (dőlt betűs)
900	2018 előtti szöveg
901	Netre kitett versszöveg
902	Idézett verssor (dőlt betűs)
903	Keresztidézet (dőlt betűs)
904	A következővel összevont vers
905	Verssor (álló betűs)
918	2019-es revízióval javított címsor
920	Kereszthivatkozás
950	Eltérő, egysoros teljes vers (pl. az ujszov.hu-ra)
1990	Több soros lábjegyzet teljes hivatkozása
1995	Szinoptikus párhuzam teljes hivatkozása
2001	Lábjegyzet a neten és a könyvben
2002	Több soros lábjegyzet szövege
2003	Több soros lábjegyzet zárósora
2004	Ismételt lábjegyzet, csak a neten
2018	2019-es revízióval javított lábjegyzetszöveg
2023	Lábjegyzet csak a könyvben
2024	Lábjegyzet csak a neten

*/

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
    ],
    'STL' => [
        'verseTypes' =>
        [
            'text' => [901],
            'footnote' => [2001, 2004, 2023]
        ],
		'textSource' => env('TEXT_SOURCE_STL')
    ]
];
