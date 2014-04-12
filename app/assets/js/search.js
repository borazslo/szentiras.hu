function change_reftrans() {
    var reftrans = "trans" + $('select#reftrans').val();
    //alert("trans"+reftrans);

    $(".trans").css("display", "none");
    $('select#in').val('');
    $("." + reftrans).css("display", "block");
}

function suggest(inputString) {
    if (inputString.length == 0) {
        $('#suggestions').fadeOut();
    } else {
        $('#texttosearch').addClass('load');
        $.post("http://szentiras.hu/autosuggest.php", {queryString: "" + inputString + "", reftrans: "" + $('#reftrans').val() + ""}, function (data) {
            if (data.length > 0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
                $('#texttosearch').removeClass('load');
            }
        });
    }
}

function fill(thisValue) {
    $('#texttosearch').val(thisValue);
    setTimeout("$('#suggestions').fadeOut();", 600);
}
