$ = require('jquery')

options = ->
  $.param(
    'headings' : $('#pdfHeadings').prop('checked')
    'nums' : $('#pdfNums').prop('checked')
    'refs' : $('#pdfRefs').prop('checked')
    'quantity' : $('#pdfQuantity').val()
  )

progressProgressBar= ($progressBar) ->
  w = parseInt($progressBar[0].style.width)
  if (w < 90)
    $progressBar.css('width', (w + 10) + '%')
    $progressBar.attr('aria-valuenow', w + 10)
    setTimeout( ->
      progressProgressBar($progressBar)
    , 750)

resetProgressBar = ($progressBar) ->
  $progressBar.attr('aria-valuenow', 40).css('width', '40%');
  $('.label', $progressBar).text('Előnézet készítése...')
  setTimeout(progressProgressBar($progressBar), 500);

done = ->
  $progressBar = $('#previewContainer .progress-bar')
  $progressBar.attr('aria-valuenow', 100).css('width','100%');
  $('.label', $progressBar).text('Előnézet kész.')

$('#pdfModal').on 'loaded.bs.modal', (event) =>
  $progressBar = $('#previewContainer .progress-bar')
  resetProgressBar($progressBar)
  ref = $('#previewContainer').data 'ref'
  translationId = $('#previewContainer').data 'translation'
  img = $("<img />").attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options());
  img.load( ->
    done()
    $("#previewContainer").append(img)
  )

  refreshPreview = ->
    resetProgressBar($('#previewContainer .progress-bar'));
    $('#previewContainer img').hide()
    img.attr('src', '/pdf/preview/'+translationId+'/'+ref+'?'+options());
    img.load ->
      $('#previewContainer .progress-bar').attr('aria-valuenow', 100).css('width', '100%');
      $('#previewContainer img').show()

  $('#pdfToggles :checkbox').change (event) ->
    refreshPreview()

  $('#pdfQuantity').change (event) ->
    refreshPreview()

  $("#pdfDownload").click (event) ->
    window.location = '/pdf/ref/'+translationId+'/'+ref+'?'+options()