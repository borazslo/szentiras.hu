@extends('layout')

@section('menu')
    <?php
        foreach ($translations as $translation) {
            $translationTitle = $translation['name']." (".$translation['abbrev'].")";
            $translationUrl = "/biblia/${translation['abbrev']}";
            $items[]  = array($translationTitle,$translationUrl);
        }
        
	$items[] = array("Újszövetség: hangfájlok", "/hang");
        $items[] = array("További fordítások", "/forditasok");
        $items[] = 'pause';
        $items[] = array("Keresés a Bibliában", '/kereses');
	
        $items[] = View::make("search")->render();
	$items[] = 'pause';
    
	$items[] = array("FEJLESZTŐKNEK", "/API");
	$items[] = array("Újdonságaink", "/info");
	
        $items[] = 'pause';
	$items[] = array("Görög újszövetségi honlap","http://www.ujszov.hu/");
	$items[] = array("Katolikus igenaptár","http://www.katolikus.hu/igenaptar/");
	$items[] = array("Zsolozsma","http://zsolozsma.katolikus.hu/");
    ?>
    @include('menu', array('items' => $items))
@stop