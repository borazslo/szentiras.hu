{{ Form::model($form, array('route' => array('search.store'))) }}
    {{ Form::text('textToSearch',Form::getValueAttribute("textToSearch"), array('onkeyup'=>'suggest(this.value)', 'size'=>10, 'maxlength'=>80, 'class'=>'alap', 'style'=>'width:92%;margin-bottom:5px')) }}
    {{ Form::hidden('reftrans') }}
    {{ Form::submit('Keresés') }}
    <div id="suggestions" class="suggestionsBox2" style="display: none;">
        <div id="suggestionsList" class="suggestionList"></div>
    </div>    
{{ Form::close() }}