let text_max_100 = 100;
$('#count_comment_limit').html(text_max_100 + ' / ' + text_max_100);

$('#comment_limit').keyup(function () {
    var text_length = $('#comment_limit').val().length;
    var text_remaining = text_max_100 - text_length;

    $('#count_comment_limit').html(text_remaining + ' / ' + text_max_100);
});

let text_max = 50;
$('#count_message').html(text_max + ' / ' + text_max);

$('#inputCategory').keyup(function () {
    var text_length = $('#inputCategory').val().length;
    var text_remaining = text_max - text_length;

    $('#count_message').html(text_remaining + ' / ' + text_max);
});

$('#inputPaymentMethod').keyup(function () {
    var text_length = $('#inputPaymentMethod').val().length;
    var text_remaining = text_max - text_length;

    $('#count_message').html(text_remaining + ' / ' + text_max);
});

$('#category').keyup(function () {
    var text_length = $('#category').val().length;
    var text_remaining = text_max - text_length;

    $('#count_message').html(text_remaining + ' / ' + text_max);
});