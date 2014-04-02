//var statusIntervalId = window.setInterval(update, 1000);

function update(resz) {
    $.ajax({
        url: 'http://szentiras.hu/igenaptar/index.php?q=_getresz&value=' + $('#'+resz).val() + '&resz=' + resz,
        dataType: 'text',
        success: function(data) {
            if (parseInt(data) == 0) {
                $("#status").css({ color: "red" }).text("offline");
            } else {
				$("#"+resz + "text").css({ color: "green" }).val(data);
            }
        }
    });
}