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
            $(toggle.selector).addClass('hidden');
            $(toggle.toggleButton).removeClass('active');
        } else {
            $(toggle.selector).removeClass('hidden');
            $(toggle.toggleButton).addClass('active');
        }

        $(toggle.toggleButton).click(function () {
            if ($(toggle.toggleButton).hasClass('active')) {
                $(toggle.selector).fadeOut(delay);
                $(toggle.selector).addClass('hidden');
                $(toggle.toggleButton).removeClass('active');
                localStorage.setItem(toggle.storageKey, 'true');
            } else {
                // special treatment for numv beacuse of the ai
                if (localStorage.getItem("aiToolsState") == 'true' && toggle.toggleButton == '#toggleNumv') {
                    $(".numchapter").removeClass('hidden');
                    $(".numchapter").fadeIn(delay);
                } else {
                    $(toggle.selector).removeClass('hidden');
                    $(toggle.selector).fadeIn(delay);
                }
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

    function ai(turnOn) {
        async function getPopoverContent(element, loadingPopover, popover) {
            if (!element.dataset.loaded) {
                loadingPopover.show();
                fetch(`/ai-tool/${element.getAttribute("data-link")}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingPopover.hide();
                        popover.setContent({ '.popover-body': data });
                        popover.show();
                        element.dataset.loaded = true;
                        popover.tip.querySelector('.btn-close').addEventListener("click", () => {
                            popover.hide();
                        });
                    })
                    .catch((e) => {
                        loadingPopover.hide();
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
    
        if (turnOn) {
            $('.parsedVerses span.numv').addClass('hidden');
            $('.parsedVerses span.numvai').removeClass('hidden');
            $('#toggleAiTools').addClass('active');
            localStorage.setItem('aiToolsState', 'true');
            const aiTriggers = document.querySelectorAll("a.numvai");
            [...aiTriggers].map(aiTrigger => {
                const loadingPopover = new bootstrap.Popover(aiTrigger,
                    {
                        trigger: 'click',
                        placement: "auto",
                        content: "Betöltés....",
                    }
                );
                const popover = new bootstrap.Popover(aiTrigger,
                    {
                        trigger: 'manual',
                        html: true,
                        placement: "auto",
                        sanitize: false
                    }
                );
                aiTrigger.addEventListener("click", () => {
                    getPopoverContent(aiTrigger, loadingPopover, popover);
                });
            });
        } else {
            if (localStorage.getItem("hideNumbers") != 'true') {
                $('.parsedVerses span.numv').removeClass('hidden');
            }
            $('.parsedVerses span.numvai').addClass('hidden');
            $('#toggleAiTools').removeClass('active');
            localStorage.setItem('aiToolsState', 'false');
        }
    }

}

function xrefPopovers() {

    async function getXrefPopoverContent(element, loadingPopover, popover) {
        if (!element.dataset.loaded) {
            loadingPopover.show();
            fetch(`/xref/${element.getAttribute("data-link")}`)
                .then(response => response.json())
                .then(data => {
                    loadingPopover.hide();
                    popover.setContent({ '.popover-body': data });
                    popover.show();
                    element.dataset.loaded = true;
                    popover.tip.querySelector('.btn-close').addEventListener("click", () => {
                        popover.hide();
                    });
                })
                .catch((e) => {
                    loadingPopover.hide();
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

    const triggers = document.querySelectorAll("a.xref");
    [...triggers].map(trigger => {
        const loadingPopover = new bootstrap.Popover(trigger,
            {
                trigger: 'click',
                placement: "auto",
                content: "Betöltés....",
            }
        );
        const popover = new bootstrap.Popover(trigger,
            {
                trigger: 'manual',
                html: true,
                placement: "auto",
                content: "Betöltés....",
                sanitize: false
            }
        );
        trigger.addEventListener("click", () => {
            getXrefPopoverContent(trigger, loadingPopover, popover);
        });
    });
}

function initPdfModal() {
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
    }
}

function initQrModal() {
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
}

function footnotePopovers() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}

initToggler();
footnotePopovers();
xrefPopovers();
initQrModal();
initPdfModal();