$ = require('jquery')
trackAdLink = (url, adId) ->
  ga('send', 'event', 'ad', adId, url,
    'hitCallback': ->
      # callback not needed, as page is opened in new window
  )

$("a[data-ad]").click ->
  trackAdLink($(this).attr('href'), $(this).data('ad'))
  true