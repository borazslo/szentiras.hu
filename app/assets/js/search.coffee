define ['jquery', 'typeahead', 'bloodhound'], ->

  suggester = new Bloodhound(
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: '/kereses/suggest?textToSearch=%QUERY'
  )

  suggester.initialize()

  $('#quickSearch .typeahead').typeahead(null, {
    name: 'suggester'
    displayKey: 'value'
    highlight: true
    minLength: 3
    source: suggester.ttAdapter()
    templates:
      header: (context) ->
        return '<p class="text-center"><a href="/kereses/search?textToSearch='+context.query+'">Részletes keresés &gt;</a></p><hr>'
  });
