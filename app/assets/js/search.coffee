define ['jquery', 'typeahead', 'bloodhound'], ->
  freeTextSuggester = new Bloodhound(
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: '/kereses/suggest?textToSearch=%QUERY'
  )

  refSuggester = new Bloodhound(
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: '/kereses/suggest?refToSearch=%QUERY'
  );

  freeTextSuggester.initialize()
  refSuggester.initialize()

  $('#quickSearch .typeahead').typeahead(
    {
      highlight: true
    }
    {
      name: 'refResults'
      minLength: 2
      source: refSuggester.ttAdapter()
      templates:
        suggestion: (suggestion) ->
          return '<p>Igehely: '+suggestion.value+'</p>'
    }
    {
      name: 'textResults'
      displayKey: 'value'
      minLength: 3
      source: freeTextSuggester.ttAdapter()
      templates:
        header: (context) ->
          return '<p class="text-center"><a href="/kereses/search?textToSearch=' + context.query + '">Részletes keresés &gt;</a></p><hr>'
    })
