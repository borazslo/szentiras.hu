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
        ai(true);
    } else {
        ai(false);
    }

    $('#toggleAiTools').click(function() {
        if ($('#toggleAiTools').hasClass('active')) {
            ai(false);
        } else {
            ai(true);
        }
    });

    async function getPopoverContent(element) {
        const popover = bootstrap.Popover.getOrCreateInstance(element);
        if (!element.dataset.loaded) {
            fetch(`/ai-tool/${element.getAttribute("data-link")}`)
            .then(response => response.json())
            .then(data => {
                popover.setContent({ '.popover-body': data});
                element.dataset.loaded = true;
            })
            .catch( (e) => {
                console.log("Error loading content", e);
                popover.setContent({ '.popover-body': ":( Hiba a betöltés során"});
                element.dataset.loaded = true;
            });
        }
    }

    function ai(turnOn) {
        if (turnOn) {
            $('.parsedVerses span.numv').hide();
            $('.parsedVerses span.numvai').show();
            $('#toggleAiTools').addClass('active');
            localStorage.setItem('aiToolsState', 'true');
            aiTriggers = document.querySelectorAll("a.numvai");
            [...aiTriggers].map(aiTrigger => {
                new bootstrap.Popover(aiTrigger,
                    {
                        trigger: 'click manual',
                        html: true,
                        placement: "auto",
                        content: "Betöltés....",
                        sanitize: false
                    }
                );
                aiTrigger.addEventListener("shown.bs.popover", () => {
                    getPopoverContent(aiTrigger);
                 })
            });
        } else {
            if (localStorage.getItem("hideNumbers") != 'true') {
                $('.parsedVerses span.numv').show();
            }
            $('.parsedVerses span.numvai').hide();
            $('#toggleAiTools').removeClass('active');
            localStorage.setItem('aiToolsState', 'false');
        }
    }

}