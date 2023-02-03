/****************************************
    
                WEB PAGE URLS

****************************************/
var show_logging_in_console = "true"

var hostweb = "http://dita.local";
//var hostweb = "http://pjdigitalpool";

// LOGIN PAGE URL
var web_login_url = `${hostweb}/admin/login`;

// DASHBOARD PAGE URL
var web_home_url = `${hostweb}/admin/users/list`;



/****************************************
    
                API URLS

****************************************/

var host_api = "http://dita.local";
//var host_api = "http://pjdigitalpool";

// GET LOGIN CODE URL
var get_login_code_url = `${host_api}/api/v1/user/send-login-code`;

// GET PAYMENT URL API URL
var get_payment_url = `${host_api}/api/v1/user/get-payment-url`;




// LOGGING INFORMATION
function show_log_in_console(log){
    if(show_logging_in_console){
        console.log(log);
    }
}


// CHECKING IF USER HAS AN API TOKEN
function user_has_api_token()
{
    if(
        (localStorage.getItem("admin_access_token") != null && localStorage.getItem("admin_access_token").trim() != "")
         && (localStorage.getItem("admin_firstname") != null && localStorage.getItem("admin_firstname").trim() != "")
         && (localStorage.getItem("admin_surname") != null && localStorage.getItem("admin_surname").trim() != "")
    
    )
    {
        return true;
    } else {
        return false;
    }
}


// LOGGING USER OUT BY DELETING ACCESS TOKEN
function delete_user_authentication()
{
    localStorage.clear();
    show_log_in_console("user_deleted");
}

function user_token_is_no_longer_valid()
{
    delete_user_authentication();
    redirect_to_next_page(web_login_url, false); 
}

function sign_out_success(response)
{
    delete_user_authentication(); 
    user_token_is_no_longer_valid()
}

function sign_out_error(errorThrown)
{
    show_notification("msg_holder", "danger", "Error", errorThrown);
    fade_out_loader_and_fade_in_form("logoutloader", "logoutspan");   
}

function sign_me_out()
{    
    fade_in_loader_and_fade_out_form("logoutloader", "logoutspan");     
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    send_restapi_request_to_server_from_form("get", api_logout_url, bearer, "", "json", sign_out_success, sign_out_error);
}

function hide_notification(){
    document.getElementById('msg_div').style.display = "none";
}

// SHOWING A NOTIFICATION ON THE SCREEN
function show_notification(id, type, title, message)
{
    $('#'+id).html(
        '<div id="msg_div" class="' + type + '"><b>' + title +' </b> '+ message +'<a id="close-bar" onclick="hide_notification();">×</a></div>'
    );

    setTimeout(function(){ $('#close-bar').click(); }, 5000);
}


// SHOWING A LOADER AND DISAPPEARING FORM
function fade_in_loader_and_fade_out_form(loader_id, form_id)
{
    $('#'+form_id).hide();
    $('#'+loader_id).show();
}

// SHOWING A FORM AND DISAPPEARING LOADER
function fade_out_loader_and_fade_in_form(loader_id, form_id)
{
    $('#'+loader_id).hide();
    $('#'+form_id).show();
}

// SENDING USER TO NEW PAGE
function redirect_to_next_page(url, can_return_to_page)
{
    if(can_return_to_page){// Simulate a mouse click:
        setTimeout(window.location.href = url, 7000);
    } else {
        setTimeout(window.location.replace(url), 7000);
    }
}

function get_json_from_form_data(e)
{
    const formData = new FormData(e.target);
    var object = {};
    formData.forEach(function(value, key){
        object[key] = value;
    });
    return JSON.stringify(object);

}

function send_request_to_server_from_form(method, url_to_server, form_data, data_type, success_response_function, error_response_function)
{
    show_log_in_console("form_data: " + form_data);
    $.ajax({
        type: method,
        url: url_to_server,
        data:  form_data,
        dataType: data_type,
        success: function(response){ 
            show_log_in_console(response);
            if(response.status === true || response.status === "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            show_log_in_console(errorThrown);
            error_response_function(errorThrown);
        }
    });
}

function send_restapi_request_to_server_from_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        headers: {
            'Authorization': authorization
         },
        data:  form_data,
        dataType: data_type,
        success: function(response){ 
            show_log_in_console("response");
            show_log_in_console(response);

            if(response == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            } 
            if(response.status.trim() == "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            show_log_in_console("errorThrown");
            show_log_in_console(errorThrown);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(errorThrown);
        }
    });
}

function send_file_upload_restapi_request_to_server_from_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        headers: {
            'Authorization': authorization
         },
        data:  form_data,
        contentType: false,
        processData: false,
        async: true,
        cache: false,
        timeout: 600000,
        success: function(response){ 
            show_log_in_console(response);

            if(response == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            } 
            if(response.status.trim() == "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            show_log_in_console(errorThrown);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(errorThrown);
        }
    });
}