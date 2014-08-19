define ['jquery'], ->

  options = ->
    $.param(
      'headings' : $('#pdfHeadings').prop('checked')
      'nums' : $('#pdfNums').prop('checked')
      'refs' : $('#pdfRefs').prop('checked')
    )

  $('#pdfModal').on 'loaded.bs.modal', (event) =>
    ref = $('#previewContainer').data 'ref'
    translationId = $('#previewContainer').data 'translation'
    img = $("<img />").attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options());
    img.load( ->
      $('#previewContainer .fa-spin').hide()
      $("#previewContainer").append(img)
    )

    $('#pdfToggles :checkbox').change (event) ->
      $('#previewContainer .fa-spin').show()
      $('#previewContainer img').hide()
      img.attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options());
      img.load ->
        $('#previewContainer .fa-spin').hide()
        $('#previewContainer img').show()

    $("#pdfDownload").click (event) ->
      window.location = '/pdf/ref/'+translationId+'/'+ref+'?'+options()