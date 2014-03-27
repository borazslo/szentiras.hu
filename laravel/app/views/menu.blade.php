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
