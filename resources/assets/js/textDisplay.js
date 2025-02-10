function initToggler() {
    var delay = 400;
    var toggles = [
        {
            storageKey: 'hideHeadings',
            selector: '.heading',
            toggleButton: '#toggleHeadings'
        },
        {
            storageKey: 'hideNumbers',
            selector: '.numv, .numchapter',
            toggleButton: '#toggleNumv'
        },
        {
            storageKey: 'hideXrefs',
            selector: '.xref',
            toggleButton: '#toggleXrefs'
        }
    ];

    toggles.forEach(function(toggle) {
        var state = localStorage.getItem(toggle.storageKey);
        if (state === 'true') {
            $(toggle.selector).hide();
            $(toggle.toggleButton).removeClass('active');
        } else {
            $(toggle.selector).show();
            $(toggle.toggleButton).addClass('active');
        }

        $(toggle.toggleButton).click(function() {
            if ($(toggle.toggleButton).hasClass('active')) {
                $(toggle.selector).fadeOut(delay);
                $(toggle.toggleButton).removeClass('active');
                localStorage.setItem(toggle.storageKey, 'true');
            } else {
                $(toggle.selector).fadeIn(delay);
                $(toggle.toggleButton).addClass('active');
                localStorage.setItem(toggle.storageKey, 'false');
            }
        });
    });

    var aiState = localStorage.getItem('aiToolsState');
    if (aiState === 'true') {
        $('.parsedVerses span.numv').addClass('ai');
        $('#toggleAiTools').addClass('active');
    } else {
        $('.parsedVerses span.numv').removeClass('ai');
        $('#toggleAiTools').removeClass('active');
    }

    $('#toggleAiTools').click(function() {
        if ($('#toggleAiTools').hasClass('active')) {
            $('.parsedVerses span.numv').removeClass('ai');
            $('#toggleAiTools').removeClass('active');
            localStorage.setItem('aiToolsState', 'false');
        } else {
            $('.parsedVerses span.numv').addClass('ai');
            $('#toggleAiTools').addClass('active');
            localStorage.setItem('aiToolsState', 'true');
        }
    });
}