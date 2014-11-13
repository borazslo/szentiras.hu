define ['jquery'], ->
  trackSimonTLLink = (url) ->
    ga('send', 'event', 'simonTL', 'click', url,
      'hitCallback': ->
        # callback not needed, as page is opened in new window
    )

  $("a.simonTL").click ->
    trackSimonTLLink($(this).attr('href'))
    true