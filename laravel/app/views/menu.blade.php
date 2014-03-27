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

    $items[] = View::make("searchForm", array('form' => $searchForm))->render();
    $items[] = 'pause';

    $items[] = array("FEJLESZTŐKNEK", "/API");
    $items[] = array("Újdonságaink", "/info");

    $items[] = 'pause';
    $items[] = array("Görög újszövetségi honlap","http://www.ujszov.hu/");
    $items[] = array("Katolikus igenaptár","http://www.katolikus.hu/igenaptar/");
    $items[] = array("Zsolozsma","http://zsolozsma.katolikus.hu/");
?>

<table border='0' cellpadding='0' cellspacing='0' width='750'>
    @foreach($items as $item)
        @if ($item == 'pause')
            <tr><td style='height:15px'></tr>
        @elseif (!is_array($item))
            <tr><td class='menu' >{{ $item }}</td></tr>
        @else
            <tr><td style='height:3px'></tr>
            <tr>
                <td background='/img/vmenucolorbg.gif' width='140' align='left' class='menu'>
                    {{ link_to($item[1], $item[0], array('class'=>'menulink')) }}
                </td>
            </tr>         
        @endif
    @endforeach
</table>
