define ['jquery'], ->

  initToggler = ->
    delay = 400;
    $('#toggleNumv').click ->
      if ($('#toggleNumv').hasClass('active'))
        $('.numv').hide(delay)
        $('#toggleNumv').removeClass('active')
      else
        $('.numv').show(delay)
        $('#toggleNumv').addClass('active')
    $('#toggleXrefs').click ->
      if ($('#toggleXrefs').hasClass('active'))
        $('.xref').hide(delay)
        $('#toggleXrefs').removeClass('active')
      else
        $('.xref').show(delay)
        $('#toggleXrefs').addClass('active')