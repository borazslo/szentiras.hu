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
    source: suggester.ttAdapter()
  });
