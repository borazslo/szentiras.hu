const initToggler = function () {
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

    toggles.forEach(function (toggle) {
        var state = localStorage.getItem(toggle.storageKey);
        if (state === 'true') {
            $(toggle.selector).hide();
            $(toggle.toggleButton).removeClass('active');
        } else {
            $(toggle.selector).show();
            $(toggle.toggleButton).addClass('active');
        }

        $(toggle.toggleButton).click(function () {
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

    $('#toggleAiTools').click(function () {
        if ($('#toggleAiTools').hasClass('active')) {
            ai(false);
        } else {
            ai(true);
        }
    });

    async function getPopoverContent(element, loadingPopover, popover) {
        if (!element.dataset.loaded) {
            loadingPopover.show();
            fetch(`/ai-tool/${element.getAttribute("data-link")}`)
                .then(response => response.json())
                .then(data => {
                    popover.setContent({ '.popover-body': data });
                    loadingPopover.dispose();
                    popover.show();
                    element.dataset.loaded = true;                    
                    popover.tip.querySelector('.btn-close').addEventListener("click", () => {
                         popover.hide();
                    });
                })
                .catch((e) => {
                    console.log("Error loading content", e);
                    popover.setContent({ '.popover-body': ":( Hiba a betöltés során" });
                    setTimeout(() => { popover.hide() }, 1000);
                    element.dataset.loaded = true;
                });
        } else {
            popover.show();            
            popover.tip.querySelector('.btn-close').addEventListener("click", () => {
                popover.hide();
           });

        }
    }

    function ai(turnOn) {
        if (turnOn) {
            $('.parsedVerses span.numv').hide();
            $('.parsedVerses span.numvai').show();
            $('#toggleAiTools').addClass('active');
            localStorage.setItem('aiToolsState', 'true');
            const aiTriggers = document.querySelectorAll("a.numvai");
            [...aiTriggers].map(aiTrigger => {
                const loadingPopover = new bootstrap.Popover(aiTrigger,
                    {
                        trigger: 'click',
                        html: true,
                        placement: "auto",
                        content: "Betöltés....",
                        sanitize: false
                    }
                );
                const popover = new bootstrap.Popover(aiTrigger,
                    {
                        trigger: 'manual',
                        html: true,
                        placement: "auto",
                        content: "Betöltés....",
                        sanitize: false
                    }
                );
                aiTrigger.addEventListener("click", () => {
                    getPopoverContent(aiTrigger, loadingPopover, popover);                    
                });
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

const qrModal = document.getElementById('qrModal');
if (qrModal) {
    qrModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const recipient = button.getAttribute('data-bs-view');
        fetch(`${recipient}`)
            .then(response => response.text())
            .then(data => {
                const qrModalContent = qrModal.querySelector('.modal-content');
                qrModalContent.innerHTML = `${data}`;
            })
            .catch((e) => {
                console.log("Error loading content", e);
            });
    });

}

const pdfModal = document.getElementById('pdfModal');
if (pdfModal) {
    pdfModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const recipient = button.getAttribute('data-bs-view');
        fetch(`${recipient}`)
            .then(response => response.text())
            .then(data => {
                const modalContent = pdfModal.querySelector('.modal-content');
                modalContent.innerHTML = `${data}`;
            })
            .catch((e) => {
                console.log("Error loading content", e);
            });
    });
    initPdfModal();
}

initToggler();