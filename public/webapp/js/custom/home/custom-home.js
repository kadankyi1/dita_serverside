
    $("#kw_form").submit(function(e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.
        var kw = $("#kw").val();
        redirect_to_next_page(hostweb+"/search?kw=" + kw, "true");
    
    
});