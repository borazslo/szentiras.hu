$ = require('jquery')
require('jquery-ui/autocomplete')

$('#quickSearch').autocomplete
  source: '/kereses/suggest'
  minLength: 2
  messages:
    noResults: ''
    results: ->
  select: (event, ui) ->
    window.location = ui.item.link
    return false;
.data("ui-autocomplete")._renderItem = (ul, item) ->
  if (item.cat == 'ref')
    return $("<li>").append("<a><b>Igehely: </b>"+item.label+"</a>").appendTo(ul)
  else
    return $("<li>").append("<a>" + item.label + " <i>("+ item.linkLabel + ")</i></a>").appendTo(ul)

$('#quickSearchButton').click ->
  $('#quickSearchForm').submit()