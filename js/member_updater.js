jQuery(document).ready(function ($) {
    
// when business name changes, check to see whether the business is already in the database
    $('#bus_name').change(function (evt) {
        var url = userregistrationformajax.ajaxurl;
        var name = $('#bus_name').val();
        var nonce = $('#cdashmu_user_registration_nonce').val();
        var data = {
            'action': 'cdashmu_find_existing_business',
            'nonce': nonce,
            'bus_name': name
        };
        // insert the business selection form into the page
        $.post(url, data, function (response) {
            jQuery("#business-picker").html(response);
        });

    });
	
	
	// when a business is selected, fill in the form
    $('#business-picker').on('change', 'input[name=business_id]:radio', function (evt) {
        var url = userregistrationformajax.ajaxurl;
        var business_id = $('input[name=business_id]:checked', '#cdashmu_user_registration_form').val()
        var nonce = $('#cdashmu_user_registration_nonce').val();
        var data = {
            'action': 'cdashmu_prefill_user_registration_form',
            'nonce': nonce,
            'business_id': business_id,
        };
        // fill in the form
        $.post(url, data, function (response) {
            $("#business_id").val(response.business_id);
            $("#bus_name").val(response.business_name);
        });       
        
    });
    
    //Copy the location address to the billing address based on which button is clicked.
    $("#billing_copy_message").hide();
    $(".copy_billing_address").click(function(){
        var element_id = $(this).attr('id');
        var id_string = element_id.split("_");
        var last_element = $(id_string).get(-1);
        
        var address = $("#buscontact_meta_location_" + last_element + "_address").val();                        
        var city = $("#buscontact_meta_location_" + last_element + "_city").val();                    
        var state = $("#buscontact_meta_location_" + last_element + "_state").val();                    
        var zip = $("#buscontact_meta_location_" + last_element + "_zip").val(); 
        var phone_number = $("#buscontact_meta_location_" + last_element + "_phone_0_phonenumber").val();
        var email_address = $("#buscontact_meta_location_" + last_element + "_email_0_emailaddress").val();
        
        $("#billing_address").val(address);
        $("#billing_city").val(city);
        $("#billing_state").val(state);
        $("#billing_zip").val(zip);
        $("#billing_phone").val(phone_number);
        $("#billing_email").val(email_address);
        var success_message = " Successfully copied to billing!";
        $("#billing_copy_message_"+ last_element).text($success_message).show().delay(5000).fadeOut();
    });
    
    //Adding social media fields from the front end    
    var num = 0;
    var social_media_array = {
        'avvo'      : 'Avvo',
        'facebook'  : 'Facebook',
        'flickr'    : 'Flickr',
        'google'    : 'Google +',
        'instagram' : 'Instagram',
        'linkedin'  : 'LinkedIn',
        'pinterest' : 'Pinterest',
        'tripadvisor': 'Trip Advisor',
        'tumblr'    : 'Tumblr',
        'twitter'   : 'Twitter',
        'urbanspoon': 'Urbanspoon',
        'vimeo'     : 'Vimeo',
        'website'   : 'Website',
        'youtube'   : 'YouTube',
        'yelp'      : 'Yelp'
    };    
    
    $("#add_social_media").click(function(){
        var social_id = $("#social_media div").last().attr('id');
        if(social_id){
            var social_id_string = social_id.split("_");
            num = $(social_id_string).get(-2);                        
            num++;
        }
        //alert(num);
        var new_social_media_div_id = "buscontact_meta_social_" + num + "_socialservice";
        var new_social_media_service_label_for = "buscontact_meta[social][" + num + "][socialservice]";
        var new_social_media_select_name = "buscontact_meta[social][" + num + "][socialservice]";
        var new_social_media_url_label_for = "buscontact_meta[social][" + num + "][socialurl]";
        var new_social_media_input_text_name = "buscontact_meta[social][" + num + "][socialurl]";
        var new_social_media_remove_checkbox_name = "social_media_remove_" + num;
        
        
        var new_social_media_div = $("#buscontact_meta_social_template_socialservice");
        var new_social_media_clone = $(new_social_media_div).clone(true);
        $(new_social_media_clone).appendTo(".social_media_div");
        $(new_social_media_clone).css("display", "block");
        
        $(new_social_media_clone).attr("id", new_social_media_div_id );
        $("#" + new_social_media_div_id + " label").first().attr("for", new_social_media_service_label_for);
        $("#" + new_social_media_div_id + " select").attr("name", new_social_media_select_name);
        //$("#" + new_social_media_div_id + " select").attr("name", new_social_media_select_name);
        $("#" + new_social_media_div_id + " label:nth-of-type(2)").attr("for", new_social_media_url_label_for);
        $("#" + new_social_media_div_id + " input[type='text']").attr("name", new_social_media_input_text_name);
        $("#" + new_social_media_div_id + " .remove input[type='checkbox']").attr("name", new_social_media_remove_checkbox_name);
        $("#" + new_social_media_div_id + " .remove input[type='checkbox']").attr("id", new_social_media_remove_checkbox_name);        
    });
    
    //Hide the social media div when the delete button is checked
    $(".social_media_remove").click(function(){
        //alert($(this).attr('id'));
        $(this).parent().parent().remove();        
    });

});