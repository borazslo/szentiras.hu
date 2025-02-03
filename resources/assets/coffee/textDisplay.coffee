initToggler = ->

  state = localStorage.getItem('hideHeadings')
  if state == 'true'
    $('.heading').hide()
    $('#toggleHeadings').removeClass('active')
  else
    $('.heading').show()
    $('#toggleHeadings').addClass('active')

  state = localStorage.getItem('hideNumbers')
  if state == 'true'
    $('.numv, .numchapter').hide()
    $('#toggleNumv').removeClass('active')
  else
    $('.numv, .numchapter').show()
    $('#toggleNumv').addClass('active')

  state = localStorage.getItem('hideXrefs')
  if state == 'true'
    $('.xref').hide()
    $('#toggleXrefs').removeClass('active')
  else
    $('.xref').show()
    $('#toggleXrefs').addClass('active')

  delay = 400;
  $('#toggleNumv').click ->
    if ($('#toggleNumv').hasClass('active'))
      $('.numv, .numchapter').fadeOut(delay)
      $('#toggleNumv').removeClass('active')
      localStorage.setItem('hideNumbers', 'true')
    else
      $('.numv, .numchapter').fadeIn(delay)
      $('#toggleNumv').addClass('active')
      localStorage.setItem('hideNumbers', 'false')
  $('#toggleXrefs').click ->
    if ($('#toggleXrefs').hasClass('active'))
      $('.xref').fadeOut(delay)
      $('#toggleXrefs').removeClass('active')
      localStorage.setItem('hideXrefs', 'true')
    else
      $('.xref').fadeIn(delay)
      $('#toggleXrefs').addClass('active')
      localStorage.setItem('hideXrefs', 'false')
  $('#toggleHeadings').click ->
    if ($('#toggleHeadings').hasClass('active'))
      $('.heading').fadeOut(delay)
      $('#toggleHeadings').removeClass('active')
      localStorage.setItem('hideHeadings', 'true')
    else
      $('.heading').fadeIn(delay)
      $('#toggleHeadings').addClass('active')
      localStorage.setItem('hideHeadings', 'false')


initQr = ->
  $('#qrLink').click ->
    ga('send', 'event', 'link', 'click', 'qrCode')

initToggler()
initQr()