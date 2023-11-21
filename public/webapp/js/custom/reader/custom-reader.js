$(document).ready(function () 
{
    // SUBMITTING THE LOGIN FORM TO GET API TOKEN
    
    $("#sendlogincodeform").submit(function (e) 
    {
        e.preventDefault(); 
        $('#info_text').html('Sending code...');
        fade_in_loader_and_fade_out_form("loader", "sendlogincodeform");       
        send_request_to_server_from_form("post", get_login_code_url, $("#sendlogincodeform").serialize(), "json", success_response_function, error_response_function);
    });
    
    $("#verifylogincodeform").submit(function (e) 
    {
        e.preventDefault(); 
        $('#info_text').html('Verifying code..');
        fade_in_loader_and_fade_out_form("loader", "verifylogincodeform");       
        send_request_to_server_from_form("post", verify_login_code_url, $("#verifylogincodeform").serialize(), "json", success_response_function2, error_response_function2);
    });
    
    $("#choosebookform").submit(function (e) 
    {
        e.preventDefault(); 
        $('#info_text').html('Verifying code..');
        fade_in_loader_and_fade_out_form("loader", "choosebookform");       
        var url = web_reader_url + '?trxref=' + $('#book_chosen').val() + '&reference=' + $('#book_chosen').val();
        redirect_to_next_page(url, true);
    });
    

});


function success_response_function(response)
{
    console.log(response);
    console.log(response.status);
    console.log(response.message);

    if(response.status === "success" && response.message === "Login successful"){
        console.log("here 1");
        success_response_function2(response);
    } else {
        console.log("here 2");
        $('#info_text').html('A code has been sent to your email/spam. Enter the code below to complete your login');
        $('#choosebookform').hide();
        $('#sendlogincodeform').hide();
        $('#user_email2').val($('#user_email').val());
        fade_out_loader_and_fade_in_form("loader", "verifylogincodeform"); 
    }
}

function error_response_function(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
}



function success_response_function2(response)
{
    $('#info_text').html('Choose your preferred book to read');
    $('#verifylogincodeform').hide();
    $('#sendlogincodeform').hide();

    //console.log("response.data.length: " + response.data.length);
    if(response.data.length > 0){
        $('#book_chosen').html('');
        $('#book_chosen').append(
            '<option value="">Choose Book</option>'
        );
        for (let index = 0; index < response.data.length; index++) {
            const element = response.data[index];
            /*
            console.log("element");
            console.log(element);
            console.log(element[0]);
            console.log(element[0]["book_title"]);
            console.log(element[0].book_title);
            */
            $('#book_chosen').append(
                '<option value="' + element[0].transaction_payment_ref_id +'">' + element[0].book_title +' </option>'
            );
        }
    }
    fade_out_loader_and_fade_in_form("loader", "choosebookform"); 
}

function error_response_function2(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
}

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