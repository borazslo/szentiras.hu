@extends('layout')

@section('menu')
    @include('menu')
@stop

@section('hirek')
  @foreach ($news as $n)
    <div align="center" class="menu" style="background-color:#9DA7D8;color:white;padding-left:3px;margin-bottom:3px">
      <div class="menulink clicktoopen">{{ $n->title }}</div>
      <div class="openit" style="display:none;">{{ $n->text }}</div>        
    </div>
  @endforeach
@stop

@section('content')
<br>
<blockquote>
    @foreach($cathBibles as $row)
        <p><span class='alcim'><a href='/showtrans/{{ $row->id }}' class='alcim'>{{ $row->name }} ({{ $row->denom }})</a></span>
        <br><span class='catlinksmall'>{{ $row->copyright }}</span></p>
    @endforeach
</blockquote>

<br />

<h4><a href="http://www.katolikus.hu/igenaptar/{{ date('Ym')}}.html">Napi olvasmányok</a></h4>

<p class="alcim">
    @foreach($olvasmanyok as $key => $olvasmany)
<div style='height:20px'>
    @if ($olvasmany->link != '')
    <a href='{{ $olvasmany->link }}' class='link'>{{ $olvasmany->ref }}</a>
    @else {{$olvasmany->ref}}
    @endif
    <div style='float:right;margin-top:-30px'>
        @foreach ($olvasmany->extLinks as $extLink)
        <a href="{{ $extLink->url }}" title="{{ $extLink->title }}" class="button minilink"> {{ $extLink->label }}</a>
            @endforeach
            @if ($key > count($olvasmanyok)-2)
            <a href="http://evangelium.katolikus.hu/audio/NE{{ date('Ymd') }}.mp3" class="button minilink" title="Evangélium és elmélkedés az evangelium365.hu honlapról." target="_blank">mp3</a>
            @endif
    </div>
</div>
@endforeach

<br />
<p />

<span class='alcim'><a href='/forditasok'>További fordítások</a></span>
<br>
<blockquote>
    @foreach($otherBibles as $row)
        <a href='/showtrans/{{ $row->id }}'>{{ $row->name }} ({{$row->denom}})</a>
        <br />
    @endforeach
</blockquote>
@stop

@section('abbrevlist')
    @include('bookAbbrevList')
@stop