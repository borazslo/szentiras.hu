define ['jquery'], ->

  initToggler = ->
    delay = 400;
    $('#toggleNumv').click ->
      if ($('#toggleNumv').hasClass('active'))
        $('.numv').fadeOut(delay)
        $('#toggleNumv').removeClass('active')
      else
        $('.numv').fadeIn(delay)
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
      alert('hello')

  initToggler()
  initQr()