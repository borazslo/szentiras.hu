initToggler = ->

  delay = 400;

  toggles = [
    {
      storageKey: 'hideHeadings'
      selector: '.heading'
      toggleButton: '#toggleHeadings'
    },
    {
      storageKey: 'hideNumbers'
      selector: '.numv, .numchapter'
      toggleButton: '#toggleNumv'
    },
    {
      storageKey: 'hideXrefs'
      selector: '.xref'
      toggleButton: '#toggleXrefs'
    }

  ]

  for toggle in toggles
      do (toggle) ->
        state = localStorage.getItem(toggle.storageKey)
        if state == 'true'
          $(toggle.selector).hide()
          $(toggle.toggleButton).removeClass('active')
        else
          $(toggle.selector).show()
          $(toggle.toggleButton).addClass('active')

        $(toggle.toggleButton).click ->
          if $(toggle.toggleButton).hasClass('active')
            $(toggle.selector).fadeOut(delay)
            $(toggle.toggleButton).removeClass('active')
            localStorage.setItem(toggle.storageKey, 'true')
          else
            $(toggle.selector).fadeIn(delay)
            $(toggle.toggleButton).addClass('active')
            localStorage.setItem(toggle.storageKey, 'false')

  state = localStorage.getItem('aiToolsState')
  if state == 'true'
    $('.parsedVerses span.numv').addClass('ai')
    $('#toggleAiTools').addClass('active')
  else
    $('.parsedVerses span.numv').removeClass('ai')
    $('#toggleAiTools').removeClass('active')

  $('#toggleAiTools').click ->
    if $('#toggleAiTools').hasClass('active')
      $('.parsedVerses span.numv').removeClass('ai')
      $('#toggleAiTools').removeClass('active')
      localStorage.setItem('aiToolsState', 'false')
    else
      $('.parsedVerses span.numv').addClass('ai')
      $('#toggleAiTools').addClass('active')
      localStorage.setItem('aiToolsState', 'true')

initToggler()
