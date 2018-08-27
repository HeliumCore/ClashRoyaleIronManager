let dates = [];
let pauses = [];
$(document).ready(function () {
    $.ajax({
        type: 'GET',
        url: '/query/ajax/get_player_pauses.php',
        success(data) {
            if (data === 'false')
                return;

            dates = dates.concat(JSON.parse(data));
            $("#datepicker").datepicker({
                dateFormat: "@",
                onSelect: function (dateText, inst) {
                    addOrRemoveDate(dateText);
                },
                beforeShowDay: function (date) {
                    let gotDate = $.inArray($.datepicker.formatDate($(this).datepicker('option', 'dateFormat'), date), dates);
                    if (gotDate >= 0) {
                        return [true, "ui-state-highlight"];
                    }
                    return [true, ""];
                }
            });
        }
    });
    $('#changePassBtn').click(function () {
        changePassword();
    });
});

function addOrRemoveDate(date) {
    let index = $.inArray(date, dates);
    if (index >= 0)
        dates.splice(index, 1);
    else {
        if ($.inArray(date, dates) < 0)
            dates.push(date);
    }
}

function selectDates() {
    $.ajax({
        type: "POST",
        url: "/query/ajax/insert_player_pause.php",
        data: {
            dates: dates
        },
        success() {
            window.location.reload();
        }
    })
}

function changePassword() {
    let oldPass = $('#oldPass').val();
    let newPass = $('#newPass').val();

    if (!oldPass.trim() || !newPass.trim())
        return;

    let tag = $('#playerTag').val();
    $.ajax({
        type: "POST",
        url: "query/accounts/update_password.php",
        data: {
            old: oldPass,
            new: newPass,
            tag: tag
        },
        success: function (data) {
            $('#passwordChangeForm').hide();
            if (data === 'true') {
                $('#passwordChangeSuccess').show();
                $('#passwordChangeFailed').hide();
            } else {
                $('#passwordChangeFailed').show();
                $('#passwordChangeSuccess').hide();
            }
        }
    });
}