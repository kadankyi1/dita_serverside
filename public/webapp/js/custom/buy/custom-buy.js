$(document).ready(function () 
{
    // SUBMITTING THE LOGIN FORM TO GET API TOKEN
    $("#real_buy_form").submit(function (e) 
    {
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loader", "real_buy_form");       
        send_request_to_server_from_form("post", get_payment_url, $("#real_buy_form").serialize(), "json", success_response_function, error_response_function);
    });

});

function setBuyform(x, book_full_price, book_summary_price) {
    if(x.value === "book_full"){
        $('#buyform').show();
        $('#buybtn').show();
        $('#book_amt').val(book_full_price);
    } else if(x.value === "book_summary"){
        $('#buyform').show();
        $('#buybtn').show();
        $('#book_amt').val(book_summary_price);
    } else {
        $('#buyform').hide();
        $('#buybtn').hide();
    }
}

// RESENDING THE PASSCODE
function success_response_function(response)
{
    redirect_to_next_page(response.data.authorization_url, true);
}

function error_response_function(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
}
