define ['jquery'], ->

  $('#pdfModal').on 'loaded.bs.modal', (event) =>
    ref = $('#previewContainer').data 'ref'
    translationId = $('#previewContainer').data 'translation'
    options = $.param(
      'headings' : $('#pdfHeadings').prop('checked')
      'nums' : $('#pdfNums').prop('checked')
      'refs' : $('#pdfRefs').prop('checked')
    )
    img = $("<img />").attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options)
    img.load( ->
      $('#previewContainer .fa-spin').hide()
      $("#previewContainer").append(img)
    )

    $('#pdfToggles :checkbox').change (event) ->
      options = $.param(
        'headings' : $('#pdfHeadings').prop('checked')
        'nums' : $('#pdfNums').prop('checked')
        'refs' : $('#pdfRefs').prop('checked')
      )
      $('#previewContainer .fa-spin').show()
      $('#previewContainer img').hide()
      img.attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options);
      img.load ->
        $('#previewContainer .fa-spin').hide()
        $('#previewContainer img').show()

    $("#pdfDownload").click (event) ->
      window.location = '/pdf/ref/'+translationId+'/'+ref+'?'+options