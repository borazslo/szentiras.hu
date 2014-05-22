define ['jquery.ui.autocomplete'], ->

  $('#quickSearch').autocomplete
    source: '/kereses/suggest'
    minLength: 2
    messages:
      noResults: ''
      results: ->
  .data("ui-autocomplete")._renderItem = (ul, item) ->
    if (item.cat == 'ref')
      return $("<li>").append("<a><b>Igehely: </b>"+item.label+"</a>").appendTo(ul)
    else
      return $("<li>").append("<a>"+item.label+"</a>").appendTo(ul)

  $('#quickSearchButton').click ->
    $('#quickSearchForm').submit();