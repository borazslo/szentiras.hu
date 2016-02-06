$ = require('jquery')
initToggler = ->
  delay = 400;
  $('#toggleNumv').click ->
    if ($('#toggleNumv').hasClass('active'))
      $('.numv, .numchapter').fadeOut(delay)
      $('#toggleNumv').removeClass('active')
    else
      $('.numv, .numchapter').fadeIn(delay)
      $('#toggleNumv').addClass('active')
  $('#toggleXrefs').click ->
    if ($('#toggleXrefs').hasClass('active'))
      $('.xref').fadeOut(delay)
      $('#toggleXrefs').removeClass('active')
    else
      $('.xref').fadeIn(delay)
      $('#toggleXrefs').addClass('active')

initQr = ->
  $('#qrLink').click ->
    ga('send', 'event', 'link', 'click', 'qrCode')

initToggler()
initQr()