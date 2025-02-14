import quickChapterSelector from '../quickChapterSelector.js';

quickChapterSelector(translation);
$("#showToc").click(function() {
    $(".interstitial").show();
    window.location=$(this).data('url');
});
$("#hideToc").click(function() {
    $(".interstitial").show();
    window.location=$(this).data('url');
});