<form action='/search' method='get'>
    <input type='hidden' name='q' value='searchbible'>
    <input type='hidden' id='reftrans' name='reftrans' value='".$reftrans."'>
    <input type=text name='texttosearch' id='texttosearch'  onkeyup="suggest(this.value)" size=10 maxlength=80 value='{{{ $text or ""}}}' class='alap' style='width:92%;margin-bottom:5px'>
    <input type=submit value='Keresés' class='alap'>
    <div id="suggestions" class="suggestionsBox2" style="display: none;">
        <div id="suggestionsList" class="suggestionList"></div>
    </div>
</form>
