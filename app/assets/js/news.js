$(document).ready(function() {
    $(".clicktoopen").click(function() {
        if ($(this).parent().find("div.openit").is(":hidden")) {
            $(this).parent().find("div.openit").slideDown("slow");
        } else {
            $(this).parent().find("div.openit").slideUp("slow");
        }
    });

});