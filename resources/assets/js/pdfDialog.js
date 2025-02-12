window.initPdfModalScripts = function () {

    const options = () => {
        return $.param({
            'headings': $('#pdfHeadings').prop('checked'),
            'nums': $('#pdfNums').prop('checked'),
            'refs': $('#pdfRefs').prop('checked'),
            'quantity': $('#pdfQuantity').val()
        });
    };

    const progressProgressBar = ($progressBar) => {
        let w = parseInt($progressBar[0].style.width);
        if (w < 90) {
            $progressBar.css('width', (w + 10) + '%');
            $progressBar.attr('aria-valuenow', w + 10);
            setTimeout(() => {
                progressProgressBar($progressBar);
            }, 750);
        }
    };

    const resetProgressBar = ($progressBar) => {
        $progressBar.attr('aria-valuenow', 40).css('width', '40%');
        $('.label', $progressBar).text('Előnézet készítése...');
        setTimeout(() => progressProgressBar($progressBar), 500);
    };

    const done = () => {
        const $progressBar = $('#previewContainer .progress-bar');
        $progressBar.attr('aria-valuenow', 100).css('width', '100%');
        $('.label', $progressBar).text('Előnézet kész.');
    };

    $('#pdfModal').on('shown.bs.modal', (event) => {
        const $progressBar = $('#previewContainer .progress-bar');
        resetProgressBar($progressBar);
        const ref = $('#previewContainer').data('ref');
        const translationId = $('#previewContainer').data('translation');
        const img = $("<img />");
        img.attr('src', `/pdf/preview/${translationId}/${ref}?${options()}`);

        img.on('load', () => {
            done();
            $("#previewContainer").append(img);
        });

        const refreshPreview = () => {
            resetProgressBar($('#previewContainer .progress-bar'));
            $('#previewContainer img').hide();
            img.attr('src', `/pdf/preview/${translationId}/${ref}?${options()}`);
            img.on('load', () => {
                $('#previewContainer .progress-bar').attr('aria-valuenow', 100).css('width', '100%');
                $('#previewContainer img').show();
            });
        };

        $('#pdfToggles :checkbox').change((event) => {
            refreshPreview();
        });

        $('#pdfQuantity').change((event) => {
            refreshPreview();
        });

        $("#pdfDownload").on('click', (event) => {
            window.location = `/pdf/ref/${translationId}/${ref}?${options()}`;
        });
    });
};