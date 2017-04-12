var host 	= jQuery("#host").val(),
    assets	= host+'assets/',
    url =  jQuery("#url").val()
    site = host+"index.php/";


var token = jQuery('#token'),
    token_val = token.val(),
    token_name = token.attr('name');


$("#username").on("keypress", function(event) {

    // Disallow anything not matching the regex pattern (A to Z uppercase, a to z lowercase and white space)
    // For more on JavaScript Regular Expressions, look here: https://developer.mozilla.org/en-US/docs/JavaScript/Guide/Regular_Expressions
    var englishAlphabetAndWhiteSpace = /[A-Za-z0-9]/g;

    // Retrieving the key from the char code passed in event.which
    // For more info on even.which, look here: http://stackoverflow.com/q/3050984/114029
    var key = String.fromCharCode(event.which);

    //alert(event.keyCode);

    // For the keyCodes, look here: http://stackoverflow.com/a/3781360/114029
    // keyCode == 8  is backspace
    // keyCode == 37 is left arrow
    // keyCode == 39 is right arrow
    // englishAlphabetAndWhiteSpace.test(key) does the matching, that is, test the key just typed against the regex pattern
    if (event.keyCode == 9 || event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || englishAlphabetAndWhiteSpace.test(key)) {
        return true;
    }

    // If we got this far, just return false because a disallowed key was typed.
    return false;
});

$('#mytextbox').on("paste",function(e)
{
    e.preventDefault();
});

// Ajax post
$(document).ready(function() {
    $("#login").click(function(event) {
        event.preventDefault();
        var username = $("#username").val();
        var password = $("#password").val();
        if(!username || !password){
            $('#username').addClass('has-error');
            $('#password').addClass('has-error');
            return false;
        }

        jQuery.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: {username: username, password: password, csrf_token:token_val},
            cache :false,
            success: function(data) {
                if (data.success){
                    window.location.href = host;
                } else {
                    $('#infoMessage').html(data.message);
                    $('#infoMessage').show();
                }
            }

        });
    });
    $('input').keypress(function (e) {
        if (e.which == 13) {
            $( "#login" ).trigger( "click" );
            return false;
        }
    });

});

